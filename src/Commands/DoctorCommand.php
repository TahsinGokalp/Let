<?php

namespace Lett\Commands;

use Illuminate\Console\Command;

class DoctorCommand extends Command
{
    protected $signature = 'lett:doctor';

    protected $description = 'Test lett settings';

    public function handle(): void
    {
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

        if (in_array((string) config('app.env'), config('lett.environments'), true)) {
            $this->info('✓ [Lett] Correct environment found ('.config('app.env').')');
        } else {
            $this->error('✗ [Lett] Environment ('.config('app.env')
                .') not allowed to send errors to Lett, set this in your config');
        }
    }
}
