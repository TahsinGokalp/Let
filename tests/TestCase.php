<?php

namespace TahsinGokalp\Lett\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use TahsinGokalp\Lett\LettServiceProvider;

class TestCase extends Orchestra
{

    protected function getPackageProviders($app): array
    {
        return [
            LettServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
