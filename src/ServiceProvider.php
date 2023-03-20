<?php

namespace TahsinGokalp\Lett;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Monolog\Logger;
use TahsinGokalp\Lett\Commands\DoctorCommand;
use TahsinGokalp\Lett\Commands\TestCommand;
use TahsinGokalp\Lett\Handler\LogHandler;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        // Publish configuration file
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/lett.php' => config_path('lett.php'),
            ]);
        }

        // Register facade
        if (class_exists(AliasLoader::class)) {
            $loader = AliasLoader::getInstance();
            $loader->alias('Lett', \TahsinGokalp\Lett\Facades\Lett::class);
        }

        // Register commands
        $this->commands([
            TestCommand::class,
            DoctorCommand::class,
        ]);

        // Register language files
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'lett');

        //Publish language files
        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/lett'),
        ]);

    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lett.php', 'lett');

        $this->app->singleton('lett', function () {
            return new Lett(new Client(
                config('lett.login_key', 'login_key'),
                config('lett.project_key', 'project_key')
            ));
        });

        if ($this->app['log'] instanceof LogManager) {
            $this->app['log']->extend('lett', function ($app) {
                $handler = new LogHandler(
                    $app['lett']
                );

                return new Logger('let', [$handler]);
            });
        }
    }
}
