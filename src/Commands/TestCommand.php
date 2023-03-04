<?php

namespace TahsinGokalp\Lett\Commands;

use Exception;
use Illuminate\Console\Command;
use RuntimeException;
use TahsinGokalp\Lett\Lett;

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
                $this->info('✓ [Lett] Sent exception to lett!');
            } elseif (!is_bool($response)) {
                $body = $response->getBody()->getContents();
                $body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                $this->info('✓ [Lett] Sent exception to lett with ID: '.$body['id']);
            } else {
                $this->error('✗ [Lett] Failed to send exception to lett');
            }
        } catch (Exception $ex) {
            dd($ex->getMessage());
            $this->error("✗ [Lett] Failed to send {$ex->getMessage()}");
        }

        return self::SUCCESS;
    }

    public function generateException(): ?Exception
    {
        try {
            throw new RuntimeException('This is a test exception from the Lett console');
        } catch (RuntimeException $ex) {
            return $ex;
        }
    }
}
