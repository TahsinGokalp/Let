<?php

namespace Lett;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Lett\Commands\TestCommand;
use Lett\Logger\LettHandler;
use Monolog\Logger;
use Lett\Facade;

class ServiceProvider extends BaseServiceProvider
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

        $this->app->singleton('lett', function ($app) {
            return new Lett(new \Lett\Http\Client(
                config('lett.login_key', 'login_key'),
                config('lett.project_key', 'project_key')
            ));
        });

        if ($this->app['log'] instanceof \Illuminate\Log\LogManager) {
            $this->app['log']->extend('lett', function ($app, $config) {
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
                'namespace' => '\Lett\Http\Controllers',
                'prefix' => 'lett-api',
            ],
            static function ($router) {
                require __DIR__.'/../routes/api.php';
            }
        );
    }
}
