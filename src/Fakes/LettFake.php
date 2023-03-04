<?php

namespace TahsinGokalp\Lett\Fakes;

use TahsinGokalp\Lett\Lett;
use PHPUnit\Framework\Assert as PHPUnit;
use Throwable;

class LettFake extends Lett
{

    public array $exceptions = [];

    public function assertRequestsSent(int $expectedCount): void
    {
        PHPUnit::assertCount($expectedCount, $this->exceptions);
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

    public function handle(Throwable $exception, $fileType = 'php', array $customData = [])
    {
        $this->exceptions[get_class($exception)][] = $exception;
    }
}
