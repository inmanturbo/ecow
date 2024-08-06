<?php

namespace Inmanturbo\Ecow;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Pipeline;
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
            ->hasMigration('2024_07_07_131035_create_saved_models_table')
            ->hasCommand(EcowCommand::class);
    }

    public function packageRegistering()
    {
        $this->app->singleton(Ecow::class);
    }

    public function packageBooted()
    {
        // Event::listen('eloquent.updating*', function(string $event, array $payload) {
        //     $data = [
        //         'event' => $event,
        //         'model' => $payload[0],
        //     ];

        //     app()->bind('ecow.eloquent.updating*', fn () => Collection::make([
        //             EnsureEventsAreNotReplaying::class,
        //             EnsureModelIsNotBeingUpdated::class,
        //             StoreUpdatedModels::class,
        //             UpdateModel::class,
        //         ])
        //     );

        //     $pipeline = Pipeline::send((object)$data)->through(app('ecow.eloquent.updating*'))->then(function ($data) {
        //         return false;
        //     });

        //     return $pipeline;
        // });
    }
}
