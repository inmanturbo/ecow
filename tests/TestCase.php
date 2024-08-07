<?php

namespace Inmanturbo\Ecow\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inmanturbo\Ecow\EcowServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Inmanturbo\\Ecow\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            EcowServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // config()->set('database.default', 'testing');

        // $migration = include __DIR__.'/../database/migrations/2024_07_07_131035_create_saved_models_table.php';
        // $migration->up();
    }
}
