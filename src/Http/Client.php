<?php

namespace Lett\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /** @var ClientInterface|null */
    protected ?ClientInterface $client;

    /** @var string */
    protected string $login;

    /** @var string */
    protected string $project;

    public function __construct(string $login, string $project, ClientInterface $client = null)
    {
        $this->login = $login;
        $this->project = $project;
        $this->client = $client;
    }

    /**
     * @param array $exception
     * @return PromiseInterface|ResponseInterface|null
     *
     * @throws GuzzleException
     */
    public function report(array $exception)
    {
        try {
            return $this->getGuzzleHttpClient()->request('POST', config('lett.server'), [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->login,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Let-Package',
                ],
                'json' => array_merge([
                    'project' => $this->project,
                    'additional' => [],
                ], $exception),
                'verify' => config('lett.verify_ssl'),
            ]);
        } catch (RequestException $e) {
            return $e->getResponse();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getGuzzleHttpClient()
    {
        if (! isset($this->client)) {
            $this->client = new \GuzzleHttp\Client([
                'timeout' => 15,
            ]);
        }

        return $this->client;
    }

    /**
     * @return $this
     */
    public function setGuzzleHttpClient(ClientInterface $client): Client
    {
        $this->client = $client;

        return $this;
    }
}
