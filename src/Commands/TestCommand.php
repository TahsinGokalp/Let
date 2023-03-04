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

            if (isset($response->id)) {
                $this->info('✓ [Lett] Sent exception to Let with ID: '.$response->id);
            } elseif (is_null($response)) {
                $this->info('✓ [Lett] Sent exception to Let!');
            } else {
                $this->error('✗ [Lett] Failed to send exception to Let');
            }
        } catch (Exception $ex) {
            $this->error("✗ [Lett] Failed to send {$ex->getMessage()}");
        }

        return self::SUCCESS;
    }

    public function generateException(): ?Exception
    {
        try {
            throw new RuntimeException($this->argument('exception') ?? 'This is a test exception from the Lett console');
        } catch (RuntimeException $ex) {
            return $ex;
        }
    }
}
