<?php

namespace Lett\Commands;

use Exception;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'lett:test {exception?}';

    protected $description = 'Generate a test exception and send it to lett';

    public function handle(): void
    {
        try {
            $let = app('lett');

            if (config('lett.login_key')) {
                $this->info('✓ [Lett] Found login key');
            } else {
                $this->error('✗ [Lett] Could not find your login key, set this in your .env');
            }

            if (config('lett.project_key')) {
                $this->info('✓ [Lett] Found project key');
            } else {
                $this->error('✗ [Lett] Could not find your project key, set this in your .env');
            }

            if (in_array(config('app.env'), config('lett.environments'))) {
                $this->info('✓ [Lett] Correct environment found ('.config('app.env').')');
            } else {
                $this->error('✗ [Lett] Environment ('.config('app.env').') not allowed to send errors to Let, set this in your config');
            }

            $response = $let->handle(
                $this->generateException()
            );

            if (isset($response->id)) {
                $this->info('✓ [Lett] Sent exception to Let with ID: '.$response->id);
            } elseif (is_null($response)) {
                $this->info('✓ [Lett] Sent exception to Let!');
            } else {
                $this->error('✗ [Lett] Failed to send exception to Let');
            }
        } catch (\Exception $ex) {
            $this->error("✗ [Lett] {$ex->getMessage()}");
        }
    }

    public function generateException(): ?Exception
    {
        try {
            throw new Exception($this->argument('exception') ?? 'This is a test exception from the Lett console');
        } catch (Exception $ex) {
            return $ex;
        }
    }
}
