<?php

namespace TahsinGokalp\Lett\Tests\Mocks;

use GuzzleHttp\Psr7\Response;
use JsonException;
use TahsinGokalp\Lett\Http\Client;

class LettClient extends Client
{
    protected array $requests = [];

    /**
     * @throws JsonException
     */
    public function report(array $exception): Response
    {
        $this->requests[] = $exception;

        return new Response(200, [], json_encode(['OK'], JSON_THROW_ON_ERROR));
    }

    public function requestsSent(): array
    {
        return $this->requests;
    }
}
