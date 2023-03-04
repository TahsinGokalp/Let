<?php

namespace TahsinGokalp\Lett;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TahsinGokalp\Lett\Commands\LettCommand;

class LettServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('lett')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_lett_table')
            ->hasCommand(LettCommand::class);
    }
}
