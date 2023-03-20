<?php

namespace TahsinGokalp\Lett\Tests\Mocks;

use GuzzleHttp\Psr7\Response;
use JsonException;
use TahsinGokalp\Lett\Client;
use TahsinGokalp\LettConstants\Enum\ApiResponseCodeEnum;

class LettClient extends Client
{
    protected array $requests = [];

    /**
     * @throws JsonException
     */
    public function report(array $exception): Response
    {
        $this->requests[] = $exception;

        return new Response(200, [], json_encode([
            'message' => trans('lett-constants::' . ApiResponseCodeEnum::Success->name),
            'code' => ApiResponseCodeEnum::Success->value,
        ], JSON_THROW_ON_ERROR));
    }

    public function requestsSent(): array
    {
        return $this->requests;
    }
}
