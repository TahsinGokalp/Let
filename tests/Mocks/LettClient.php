<?php

namespace Lett\Tests\Mocks;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

class LettClient extends \Lett\Http\Client
{
    public const RESPONSE_ID = 'test';

    /** @var array */
    protected array $requests = [];

    /**
     * @param array $exception
     * @throws \JsonException
     */
    public function report(array $exception): Response
    {
        $this->requests[] = $exception;

        return new Response(200, [], json_encode(['id' => self::RESPONSE_ID], JSON_THROW_ON_ERROR));
    }

    public function assertRequestsSent(int $expectedCount): void
    {
        Assert::assertCount($expectedCount, $this->requests);
    }
}
