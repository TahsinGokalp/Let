<?php

namespace TahsinGokalp\Lett;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

class Client
{
    protected PendingRequest $client;

    protected string $login;

    protected string $project;

    private int $timeout;

    public function __construct(string $login, string $project, PendingRequest $client = null)
    {
        $this->login = $login;
        $this->project = $project;
        $this->timeout = config('lett.timeout');
        $this->client = $client ?: Http::timeout($this->timeout);
    }

    public function report(array $exception): ?ResponseInterface
    {
        try {
            return $this->getHttpClient()
                ->withToken($this->login)
                ->asJson()
                ->acceptJson()
                ->withUserAgent('Lett-Package')
                ->when(
                    ! config('lett.verify_ssl'),
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
                )->toPsrResponse();
        } catch (RequestException $e) {
            return $e->getResponse();
        } catch (GuzzleException|Exception) {
            return null;
        }
    }

    public function getHttpClient(): PendingRequest
    {
        return $this->client;
    }

    public function setHttpClient(\GuzzleHttp\Client $client): self
    {
        $this->client = Http::timeout($this->timeout)->setClient($client);

        return $this;
    }
}
