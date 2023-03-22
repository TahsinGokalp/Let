<?php

namespace TahsinGokalp\Lett;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use TahsinGokalp\Lett\Concerns\Lettable;
use TahsinGokalp\Lett\Events\JsonDecodeException;
use TahsinGokalp\Lett\Events\SkipEnvironment;
use TahsinGokalp\Lett\Events\SkipException;
use TahsinGokalp\Lett\Events\SleepingException;
use Throwable;

class Lett
{
    private Client $client;

    private array $blacklist;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->blacklist = array_map(static function ($blacklist) {
            return strtolower($blacklist);
        }, config('lett.blacklist', []));
    }

    public function handle(Throwable $exception): mixed
    {
        $data = $this->getExceptionData($exception);

        if ($this->isSkipEnvironment() || $this->isSkipException($data['class'])
            || $this->isSleepingException($data)) {
            return false;
        }

        $rawResponse = $this->logError($data);

        if (! $rawResponse) {
            return false;
        }

        try {
            if (! method_exists($rawResponse, 'getBody')) {
                throw new JsonException;
            }

            $body = $rawResponse->getBody();

            if (! method_exists($body, 'getContents')) {
                throw new JsonException;
            }

            $response = json_decode($body->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            event(new JsonDecodeException);

            return false;
        }

        if (config('lett.sleep') !== 0) {
            $this->addExceptionToSleep($data);
        }

        return $response;
    }

    public function isSkipEnvironment(): bool
    {
        if (count(config('lett.environments')) > 0 && in_array((string) App::environment(), config('lett.environments'), true)) {
            return false;
        }

        event(new SkipEnvironment);

        return true;
    }

    public function getExceptionData(Throwable $exception): array
    {
        $data = [];

        $data['environment'] = App::environment();
        $data['host'] = Request::server('SERVER_NAME');
        $data['method'] = Request::method();
        $data['fullUrl'] = Request::fullUrl();
        $data['exception'] = $exception->getMessage();
        $data['error'] = $exception->getTraceAsString();
        $data['line'] = $exception->getLine();
        $data['file'] = $exception->getFile();
        $data['class'] = get_class($exception);
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

        $data['executor'] = [];

        $lines = file($data['file']);

        if ($lines !== false) {
            if (count($lines) < $count) {
                $count = count($lines) - $data['line'];
            }

            for ($i = -1 * abs($count); $i <= abs($count); $i++) {
                $data['executor'][] = $this->getLineInfo($lines, (int) $data['line'], (int) $i);
            }
        }

        $data['executor'] = array_filter($data['executor']);

        // Get project version
        $data['project_version'] = config('lett.project_version');

        // to make symfony exception more readable
        if ($data['class'] === 'Symfony\Component\Debug\Exception\FatalErrorException') {
            preg_match("~^(.+)' in ~", $data['exception'], $matches);
            if (isset($matches[1])) {
                $data['exception'] = $matches[1];
            }
        }

        return $data;
    }

    public function filterParameterValues(array $parameters): array
    {
        return collect($parameters)->map(function ($value) {
            if ($this->shouldParameterValueBeFiltered($value)) {
                return '...';
            }

            return $value;
        })->toArray();
    }

    public function shouldParameterValueBeFiltered(mixed $value): bool
    {
        return $value instanceof UploadedFile;
    }

    public function filterVariables(mixed $variables): array
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

    public function isSkipException(string $exceptionClass): bool
    {
        if (in_array((string) $exceptionClass, config('lett.except'), true)) {
            event(new SkipException);

            return true;
        }

        return false;
    }

    public function isSleepingException(array $data): bool
    {
        if ((int) config('lett.sleep', 0) === 0 || ! Cache::has($this->createExceptionString($data))) {
            return false;
        }
        event(new SleepingException);

        return true;
    }

    public function getUser(): ?array
    {
        if (function_exists('auth') && (app() instanceof Application && auth()->check())) {
            $user = auth()->user();

            if ($user instanceof Lettable) {
                return $user->toLett();
            }

            if ($user instanceof Model) {
                return $user->toArray();
            }
        }

        return null;
    }

    public function addExceptionToSleep(array $data): bool
    {
        $exceptionString = $this->createExceptionString($data);

        return Cache::put($exceptionString, $exceptionString, config('lett.sleep'));
    }

    private function getLineInfo(array $lines, int $line, int $i): array
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

    private function createExceptionString(array $data): string
    {
        return 'lett.' . Str::slug($data['host'] . '_' . $data['method'] .
                '_' . $data['exception'] . '_' . $data['line'] . '_'
                . $data['file'] . '_' . $data['class']);
    }

    private function logError(array $exception): ResponseInterface|null
    {
        return $this->client->report([
            'exception' => $exception,
            'user' => $this->getUser(),
        ]);
    }
}
