<?php

namespace Inmanturbo\Ecow;

use Inmanturbo\Ecow\Commands\EcowCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EcowServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('ecow')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_ecow_table')
            ->hasCommand(EcowCommand::class);
    }
}
