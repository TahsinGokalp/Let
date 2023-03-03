<?php

namespace Lett\Tests;

use Illuminate\Foundation\Application;
use Lett\LettServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [LettServiceProvider::class];
    }
}
