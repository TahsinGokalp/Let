<?php

namespace TahsinGokalp\Lett\Commands;

use Exception;
use Illuminate\Console\Command;
use RuntimeException;
use TahsinGokalp\Lett\Lett;
use TahsinGokalp\LettConstants\Enum\ApiResponseCodeEnum;

class TestCommand extends Command
{
    public $signature = 'lett:test';

    public $description = 'Generate a test exception and send it to lett';

    public function handle(): int
    {
        try {
            /* @var Lett $lett*/
            $lett = app('lett');

            $response = $lett->handle(
                $this->generateException()
            );

            if (is_null($response)) {
                $this->info(__('Sent exception to lett'));
            } elseif (! is_bool($response)) {
                $response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                if ((int)$response['code'] === ApiResponseCodeEnum::Success->value) {
                    $this->info(__('Sent exception to lett'));
                } else {
                    $this->error(__('Failed to send exception to lett'));
                }
            } else {
                $this->error(__('Failed to send exception to lett'));
            }
        } catch (Exception $ex) {
            $this->error(__('Failed to send exception to lett')." {$ex->getMessage()}");
        }

        return self::SUCCESS;
    }

    public function generateException(): ?Exception
    {
        try {
            throw new RuntimeException(__('This is a test exception from the Lett console'));
        } catch (RuntimeException $ex) {
            return $ex;
        }
    }
}
