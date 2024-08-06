<?php

namespace Inmanturbo\Ecow;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Pipeline;
use Inmanturbo\Ecow\Commands\EcowCommand;
use Inmanturbo\Ecow\Pipeline\CreateModel;
use Inmanturbo\Ecow\Pipeline\CreateSavedModel;
use Inmanturbo\Ecow\Pipeline\EnsureEventsAreNotReplaying;
use Inmanturbo\Ecow\Pipeline\EnsureModelDoesNotAlreadyExist;
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
            ->hasMigration('2024_07_07_131035_create_saved_models_table')
            ->hasCommand(EcowCommand::class);
    }

    public function packageRegistering()
    {
        $this->app->singleton(Ecow::class);
    }

    public function packageBooted()
    {
        Facades\Ecow::bootListeners();
    }
}
