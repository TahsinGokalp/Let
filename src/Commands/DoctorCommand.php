<?php

namespace TahsinGokalp\Lett\Commands;

use Illuminate\Console\Command;

class DoctorCommand extends Command
{
    public $signature = 'lett:doctor';

    public $description = 'Test lett settings';

    public function handle(): int
    {
        if (config('lett.login_key')) {
            $this->info(__('Found API key'));
        } else {
            $this->error(__('Could not find your API key, set this in your .env'));
        }

        if (config('lett.project_key')) {
            $this->info(__('Found project key'));
        } else {
            $this->error(__('Could not find your project key, set this in your .env'));
        }

        if (in_array((string) config('app.env'), config('lett.environments'), true)) {
            $this->info(__('Correct environment found ()', ['environment' => config('app.env')]));
        } else {
            $this->error(__('Environment () not allowed to send errors to Lett, set this in your config', ['environment' => config('app.env')]));
        }

        return self::SUCCESS;
    }
}
