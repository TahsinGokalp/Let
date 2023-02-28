<?php

namespace Let\Commands;

use Exception;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'let:test {exception?}';

    protected $description = 'Generate a test exception and send it to let';

    public function handle()
    {
        try {
            $let = app('let');

            if (config('let.login_key')) {
                $this->info('✓ [Let] Found login key');
            } else {
                $this->error('✗ [Let] Could not find your login key, set this in your .env');
            }

            if (config('let.project_key')) {
                $this->info('✓ [Let] Found project key');
            } else {
                $this->error('✗ [Let] Could not find your project key, set this in your .env');
            }

            if (in_array(config('app.env'), config('let.environments'))) {
                $this->info('✓ [Let] Correct environment found ('.config('app.env').')');
            } else {
                $this->error('✗ [Let] Environment ('.config('app.env').') not allowed to send errors to Let, set this in your config');
            }

            $response = $let->handle(
                $this->generateException()
            );

            if (isset($response->id)) {
                $this->info('✓ [Let] Sent exception to Let with ID: '.$response->id);
            } elseif (is_null($response)) {
                $this->info('✓ [Let] Sent exception to Let!');
            } else {
                $this->error('✗ [Let] Failed to send exception to Let');
            }
        } catch (\Exception $ex) {
            $this->error("✗ [Let] {$ex->getMessage()}");
        }
    }

    public function generateException(): ?Exception
    {
        try {
            throw new Exception($this->argument('exception') ?? 'This is a test exception from the Let console');
        } catch (Exception $ex) {
            return $ex;
        }
    }
}
