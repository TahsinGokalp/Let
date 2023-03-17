<?php

namespace TahsinGokalp\Lett\Http;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    protected ?ClientInterface $client;

    protected string $login;

    protected string $project;

    public function __construct(string $login, string $project, ClientInterface $client = null)
    {
        $this->login = $login;
        $this->project = $project;
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     */
    public function report(array $exception): PromiseInterface|ResponseInterface|null
    {
        if ($this->getGuzzleHttpClient() === null) {
            return null;
        }

        try {
            return $this->getGuzzleHttpClient()->request('POST', config('lett.server'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->login,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Lett-Package',
                ],
                'json' => array_merge([
                    'project' => $this->project,
                    'additional' => [],
                ], $exception),
                'verify' => config('lett.verify_ssl'),
            ]);
        } catch (RequestException $e) {
            return $e->getResponse();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getGuzzleHttpClient(): \GuzzleHttp\Client|ClientInterface|null
    {
        if (! isset($this->client)) {
            $this->client = new \GuzzleHttp\Client([
                'timeout' => 15,
            ]);
        }

        return $this->client;
    }

    public function setGuzzleHttpClient($client): static
    {
        $this->client = $client;

        return $this;
    }
}
