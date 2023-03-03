<?php

namespace Lett;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Lett\Http\Client;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Lett
{
    /** @var Client */
    private Client $client;

    /** @var array */
    private array $blacklist = [];

    /** @var null|string */
    private ?string $lastExceptionId;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->blacklist = array_map(static function ($blacklist) {
            return strtolower($blacklist);
        }, config('lett.blacklist', []));
    }

    /**
     * @param string $fileType
     * @return bool|mixed
     */
    public function handle(Throwable $exception, string $fileType = 'php', array $customData = [])
    {
        $data = $this->getExceptionData($exception);

        if ($this->isSkipEnvironment() || $this->isSkipException($data['class'])
            || $this->isSleepingException($data)) {
            return false;
        }

        if ((string)$fileType === 'javascript') {
            $data['fullUrl'] = $customData['url'];
            $data['file'] = $customData['file'];
            $data['file_type'] = $fileType;
            $data['error'] = $customData['message'];
            $data['exception'] = $customData['stack'];
            $data['line'] = $customData['line'];
            $data['class'] = null;

            $count = config('lett.lines_count');

            if ($count > 50) {
                $count = 12;
            }

            $lines = file($data['file']);
            $data['executor'] = [];

            for ($i = -1 * abs($count); $i <= abs($count); $i++) {
                $currentLine = $data['line'] + $i;

                $index = $currentLine - 1;

                if (! array_key_exists($index, $lines)) {
                    continue;
                }

                $data['executor'][] = [
                    'line_number' => $currentLine,
                    'line' => $lines[$index],
                ];
            }

            $data['executor'] = array_filter($data['executor']);
        }

        $rawResponse = $this->logError($data);

        if (! $rawResponse) {
            return false;
        }

        try {
            $response = json_decode($rawResponse->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return false;
        }

        if (isset($response->id)) {
            $this->setLastExceptionId($response->id);
        }

        if (config('lett.sleep') !== 0) {
            $this->addExceptionToSleep($data);
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function isSkipEnvironment(): bool
    {
        if (count(config('lett.environments')) === 0) {
            return true;
        }

        if (in_array(App::environment(), config('lett.environments'))) {
            return false;
        }

        return true;
    }

    private function setLastExceptionId(?string $id): void
    {
        $this->lastExceptionId = $id;
    }

    /**
     * Get the last exception id given to us by the lett API.
     *
     * @return string|null
     */
    public function getLastExceptionId(): ?string
    {
        return $this->lastExceptionId;
    }

    /**
     * @return array
     */
    public function getExceptionData(Throwable $exception): array
    {
        $data = [];

        $data['environment'] = App::environment();
        $data['host'] = Request::server('SERVER_NAME');
        $data['method'] = Request::method();
        $data['fullUrl'] = Request::fullUrl();
        $data['exception'] = $exception->getMessage() ?? '-';
        $data['error'] = $exception->getTraceAsString();
        $data['line'] = $exception->getLine();
        $data['file'] = $exception->getFile();
        $data['class'] = get_class($exception);
        $data['release'] = config('lett.release', null);
        $data['storage'] = [
            'SERVER' => [
                'USER' => Request::server('USER'),
                'HTTP_USER_AGENT' => Request::server('HTTP_USER_AGENT'),
                'SERVER_PROTOCOL' => Request::server('SERVER_PROTOCOL'),
                'SERVER_SOFTWARE' => Request::server('SERVER_SOFTWARE'),
                'PHP_VERSION' => PHP_VERSION,
            ],
            'OLD' => $this->filterVariables(Request::hasSession() ? Request::old() : []),
            'COOKIE' => $this->filterVariables(Request::cookie()),
            'SESSION' => $this->filterVariables(Request::hasSession() ? Session::all() : []),
            'HEADERS' => $this->filterVariables(Request::header()),
            'PARAMETERS' => $this->filterVariables($this->filterParameterValues(Request::all())),
        ];

        $data['storage'] = array_filter($data['storage']);

        $count = config('lett.lines_count');

        if ($count > 50) {
            $count = 12;
        }

        $lines = file($data['file']);
        $data['executor'] = [];

        if (count($lines) < $count) {
            $count = count($lines) - $data['line'];
        }

        for ($i = -1 * abs($count); $i <= abs($count); $i++) {
            $data['executor'][] = $this->getLineInfo($lines, $data['line'], $i);
        }
        $data['executor'] = array_filter($data['executor']);

        // Get project version
        $data['project_version'] = config('lett.project_version', null);

        // to make symfony exception more readable
        if ($data['class'] == 'Symfony\Component\Debug\Exception\FatalErrorException') {
            preg_match("~^(.+)' in ~", $data['exception'], $matches);
            if (isset($matches[1])) {
                $data['exception'] = $matches[1];
            }
        }

        return $data;
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function filterParameterValues(array $parameters): array
    {
        return collect($parameters)->map(function ($value) {
            if ($this->shouldParameterValueBeFiltered($value)) {
                return '...';
            }

            return $value;
        })->toArray();
    }

    /**
     * Determines whether the given parameter value should be filtered.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function shouldParameterValueBeFiltered($value): bool
    {
        return $value instanceof UploadedFile;
    }

    /**
     * @return array
     */
    public function filterVariables($variables): array
    {
        if (is_array($variables)) {
            array_walk($variables, function ($val, $key) use (&$variables) {
                if (is_array($val)) {
                    $variables[$key] = $this->filterVariables($val);
                }
                foreach ($this->blacklist as $filter) {
                    if (Str::is($filter, strtolower($key))) {
                        $variables[$key] = '***';
                    }
                }
            });

            return $variables;
        }

        return [];
    }

    /**
     * Gets information from the line.
     *
     *
     * @return array|void
     */
    private function getLineInfo($lines, $line, $i)
    {
        $currentLine = $line + $i;

        $index = $currentLine - 1;

        if (! array_key_exists($index, $lines)) {
            return [];
        }

        return [
            'line_number' => $currentLine,
            'line' => $lines[$index],
        ];
    }

    /**
     * @return bool
     */
    public function isSkipException($exceptionClass): bool
    {
        return in_array($exceptionClass, config('lett.except'));
    }

    /**
     * @return bool
     */
    public function isSleepingException(array $data): bool
    {
        if ((int)config('lett.sleep', 0) === 0) {
            return false;
        }

        return Cache::has($this->createExceptionString($data));
    }

    /**
     * @return string
     */
    private function createExceptionString(array $data): string
    {
        return 'lett.'.Str::slug($data['host'].'_'.$data['method'].
                '_'.$data['exception'].'_'.$data['line'].'_'
                .$data['file']. '_'.$data['class']);
    }

    /**
     * @return PromiseInterface|ResponseInterface|null
     * @throws GuzzleException
     */
    private function logError($exception)
    {
        return $this->client->report([
            'exception' => $exception,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @return array|null
     */
    public function getUser(): ?array
    {
        if (function_exists('auth') && (app() instanceof \Illuminate\Foundation\Application && auth()->check())) {
            /** @var Authenticatable $user */
            $user = auth()->user();

            if ($user instanceof \Lett\Concerns\Lettable) {
                return $user->toLett();
            }

            if ($user instanceof \Illuminate\Database\Eloquent\Model) {
                return $user->toArray();
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function addExceptionToSleep(array $data): bool
    {
        $exceptionString = $this->createExceptionString($data);

        return Cache::put($exceptionString, $exceptionString, config('lett.sleep'));
    }
}
