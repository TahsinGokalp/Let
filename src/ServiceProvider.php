<?php

namespace Let;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Let\Commands\TestCommand;
use Monolog\Logger;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        // Publish configuration file
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/let.php' => config_path('let.php'),
            ]);
        }

        // Register views
        $this->app['view']->addNamespace('let', __DIR__.'/../resources/views');

        // Register facade
        if (class_exists(\Illuminate\Foundation\AliasLoader::class)) {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Let', 'Let\Facade');
        }

        // Register commands
        $this->commands([
            TestCommand::class,
        ]);

        // Map any routes
        $this->mapLetApiRoutes();

        // Create an alias to the let-js-client.blade.php include
        Blade::include('let::let-js-client', 'letJavaScriptClient');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/let.php', 'let');

        $this->app->singleton('let', function ($app) {
            return new Let(new \Let\Http\Client(
                config('let.login_key', 'login_key'),
                config('let.project_key', 'project_key')
            ));
        });

        if ($this->app['log'] instanceof \Illuminate\Log\LogManager) {
            $this->app['log']->extend('let', function ($app, $config) {
                $handler = new \Let\Logger\LetHandler(
                    $app['let']
                );

                return new Logger('let', [$handler]);
            });
        }
    }

    protected function mapLetApiRoutes()
    {
        Route::group(
            [
                'namespace' => '\Let\Http\Controllers',
                'prefix' => 'let-api',
            ],
            function ($router) {
                require __DIR__.'/../routes/api.php';
            }
        );
    }
}
