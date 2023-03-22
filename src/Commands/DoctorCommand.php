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
            $this->info(trans('lett::lett.found_api_key'));
            event(new FoundApiKey);
        } else {
            $this->error(trans('lett::lett.could_not_find_api_key'));
            event(new ApiKeyNotFound);
        }

        if (config('lett.project_key')) {
            $this->info(trans('lett::lett.found_project_key'));
            event(new FoundProjectKey);
        } else {
            $this->error(trans('lett::lett.could_not_find_project_key'));
            event(new ProjectKeyNotFound);
        }

        if (in_array((string) config('app.env'), config('lett.environments'), true)) {
            $this->info(trans('lett::lett.correct_environment_found_environment', ['environment' => config('app.env')]));
            event(new FoundEnvironment(config('app.env')));
        } else {
            $this->error(trans('lett::lett.environment_environment_not_allowed_to_send_errors_to_lett_set_this_in_your_config', ['environment' => config('app.env')]));
            event(new EnvironmentNotFound(config('app.env')));
        }

        return self::SUCCESS;
    }
}
