<?php

namespace TahsinGokalp\Lett\Tests\Fakes;

use GuzzleHttp\Psr7\Response;
use JsonException;
use TahsinGokalp\Lett\Lett;
use TahsinGokalp\LettConstants\Enum\ApiResponseCodeEnum;
use Throwable;

class LettFake extends Lett
{
    public array $exceptions = [];

    public function requestsSent(): array
    {
        return $this->exceptions;
    }

    /**
     * @throws JsonException
     */
    public function handle(Throwable $exception, $fileType = 'php', array $customData = []): mixed
    {
        $this->exceptions[get_class($exception)][] = $exception;

        return new Response(200, [], json_encode([
            'message' => trans('lett-constants::' . ApiResponseCodeEnum::Success->name),
            'code' => ApiResponseCodeEnum::Success->value,
        ], JSON_THROW_ON_ERROR));
    }
}
