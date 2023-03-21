<?php

namespace TahsinGokalp\Lett\Commands;

use Illuminate\Console\Command;
use TahsinGokalp\Lett\Events\ApiKeyNotFound;
use TahsinGokalp\Lett\Events\EnvironmentNotFound;
use TahsinGokalp\Lett\Events\FoundApiKey;
use TahsinGokalp\Lett\Events\FoundEnvironment;
use TahsinGokalp\Lett\Events\FoundProjectKey;
use TahsinGokalp\Lett\Events\ProjectKeyNotFound;

class DoctorCommand extends Command
{
    public $signature = 'lett:doctor';

    public $description = 'Test lett settings';

    public function handle(): int
    {
        if (config('lett.login_key')) {
            $this->info(__('Found API key'));
            event(new FoundApiKey());
        } else {
            $this->error(__('Could not find your API key, set this in your .env'));
            event(new ApiKeyNotFound());
        }

        if (config('lett.project_key')) {
            $this->info(__('Found project key'));
            event(new FoundProjectKey());
        } else {
            $this->error(__('Could not find your project key, set this in your .env'));
            event(new ProjectKeyNotFound());
        }

        if (in_array((string) config('app.env'), config('lett.environments'), true)) {
            $this->info(__('Correct environment found ()', ['environment' => config('app.env')]));
            event(new FoundEnvironment(config('app.env')));
        } else {
            $this->error(__('Environment () not allowed to send errors to Lett, set this in your config', ['environment' => config('app.env')]));
            event(new EnvironmentNotFound(config('app.env')));
        }

        return self::SUCCESS;
    }
}
