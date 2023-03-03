<?php

namespace Lett\Fakes;

use PHPUnit\Framework\Assert as PHPUnit;

class LettFake extends \Lett\Lett
{
    /** @var array */
    public array $exceptions = [];

    public function assertRequestsSent(int $expectedCount): void
    {
        PHPUnit::assertCount($expectedCount, $this->exceptions);
    }

    /**
     * @param mixed         $throwable
     * @param callable|null $callback
     */
    public function assertNotSent($throwable, callable $callback = null): void
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

    /**
     * @param mixed         $throwable
     * @param callable|null $callback
     */
    public function assertSent($throwable, callable $callback = null): void
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

    public function handle(\Throwable $exception, $fileType = 'php', array $customData = []): void
    {
        $this->exceptions[get_class($exception)][] = $exception;
    }
}
