<?php

namespace TahsinGokalp\Lett\Tests\Mocks;

use GuzzleHttp\Psr7\Response;
use TahsinGokalp\Lett\Http\Client;

class LettClient extends Client
{
    public const RESPONSE_ID = 'test';

    /** @var array */
    protected array $requests = [];

    /**
     * @param array $exception
     *
     * @throws \JsonException
     *
     * @return Response
     */
    public function report(array $exception): Response
    {
        $this->requests[] = $exception;

        return new Response(200, [], json_encode(['id' => self::RESPONSE_ID], JSON_THROW_ON_ERROR));
    }

    public function requestsSent(): array
    {
        return $this->requests;
    }
}
