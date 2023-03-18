<?php

namespace TahsinGokalp\Lett;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Http;

class Client
{
    protected PendingRequest|ClientInterface $client;

    protected string $login;

    protected string $project;

    private int $timeout;

    public function __construct(string $login, string $project, ClientInterface $client = null)
    {
        $this->login = $login;
        $this->project = $project;
        $this->timeout = config('lett.timeout');
        $this->client = $client ?: Http::timeout($this->timeout);
    }

    public function report(array $exception): PromiseInterface|ResponseInterface|null
    {
        if ($this->getHttpClient() === null) {
            return null;
        }

        try {
            return $this->getHttpClient()
                ->withToken($this->login)
                ->asJson()
                ->acceptJson()
                ->withUserAgent('Lett-Package')
                ->when(
                    !config('lett.verify_ssl'),
                    function ($client) {
                        $client->withoutVerifying();
                    }
                )
                ->post(
                    config('lett.server'),
                    array_merge(
                        [
                            'project' => $this->project,
                            'additional' => [],
                        ],
                        $exception
                    )
                );
        } catch (RequestException $e) {
            return $e->getResponse();
        } catch (GuzzleException|Exception) {
            return null;
        }
    }

    public function getHttpClient(): PendingRequest|ClientInterface
    {
        return $this->client;
    }

    public function setHttpClient(ClientInterface $client): self
    {
        $this->client = Http::timeout($this->timeout)->setClient($client)->buildClient();

        return $this;
    }
}
