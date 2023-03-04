<?php

namespace TahsinGokalp\Lett\Fakes;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert as PHPUnit;
use TahsinGokalp\Lett\Lett;
use TahsinGokalp\Lett\Tests\Mocks\LettClient;
use Throwable;

class LettFake extends Lett
{
    public array $exceptions = [];

    public function requestsSent(): array
    {
        return $this->exceptions;
    }

    public function assertNotSent(mixed $throwable, callable $callback = null): void
    {
        $collect = collect($this->exceptions[$throwable] ?? []);

        $callback = $callback ?: static function () {
            return true;
        };

        $filtered = $collect->filter(function ($arguments) use ($callback) {
            return $callback($arguments);
        });

        PHPUnit::assertEquals($filtered->count(), 0);
    }

    public function assertNothingSent(): void
    {
        PHPUnit::assertCount(0, $this->exceptions);
    }

    public function assertSent(mixed $throwable, callable $callback = null): void
    {
        $collect = collect($this->exceptions[$throwable] ?? []);

        $callback = $callback ?: static function () {
            return true;
        };

        $filtered = $collect->filter(function ($arguments) use ($callback) {
            return $callback($arguments);
        });

        PHPUnit::assertTrue($filtered->count() > 0);
    }

    /**
     * @throws \JsonException
     */
    public function handle(Throwable $exception, $fileType = 'php', array $customData = [])
    {
        $this->exceptions[get_class($exception)][] = $exception;
        return new Response(200, [], json_encode(['id' => LettClient::RESPONSE_ID], JSON_THROW_ON_ERROR));
    }
}
