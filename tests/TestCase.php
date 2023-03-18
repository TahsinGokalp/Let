<?php

namespace TahsinGokalp\Lett\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use TahsinGokalp\Lett\ServiceProvider;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
