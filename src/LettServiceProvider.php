<?php

namespace TahsinGokalp\Lett;

use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Monolog\Logger;
use TahsinGokalp\Lett\Commands\DoctorCommand;
use TahsinGokalp\Lett\Commands\TestCommand;
use TahsinGokalp\Lett\Http\Client;
use TahsinGokalp\Lett\Logger\LettHandler;

class LettServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        // Publish configuration file
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/lett.php' => config_path('lett.php'),
            ]);
        }

        // Register views
        $this->app['view']->addNamespace('lett', __DIR__.'/../resources/views');

        // Register facade
        if (class_exists(\Illuminate\Foundation\AliasLoader::class)) {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Lett', Facade::class);
        }

        // Register commands
        $this->commands([
            TestCommand::class,
            DoctorCommand::class,
        ]);

        // Map any routes
        $this->mapLettApiRoutes();

        // Create an alias to the lett-js-client.blade.php include
        Blade::include('lett::lett-js-client', 'lettJavaScriptClient');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lett.php', 'lett');

        $this->app->singleton('lett', function () {
            return new Lett(new Client(
                config('lett.login_key', 'login_key'),
                config('lett.project_key', 'project_key')
            ));
        });

        if ($this->app['log'] instanceof LogManager) {
            $this->app['log']->extend('lett', function ($app) {
                $handler = new LettHandler(
                    $app['lett']
                );

                return new Logger('let', [$handler]);
            });
        }
    }

    protected function mapLettApiRoutes(): void
    {
        Route::group(
            [
                'prefix'    => 'lett-api',
            ],
            static function () {
                require_once __DIR__.'/../routes/api.php';
            }
        );
    }
}
