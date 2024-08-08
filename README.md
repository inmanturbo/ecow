# ecow

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inmanturbo/ecow.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/ecow)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/ecow/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inmanturbo/ecow/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/ecow/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inmanturbo/ecow/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inmanturbo/ecow.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/ecow)

## Eloquent copy-on-write: automatically copy all model changes to a separate table.

<img src="art/ecow.svg" width="200px" alt="ecow" />

Artwork by DALL-E

## Installation

You can install the package via composer:

```bash
composer require inmanturbo/ecow
```

You can run the migrations with:

```bash
php artisan ecow:migrate
```
You can run the migrations with:

```bash
php artisan ecow:migrate
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="ecow-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ecow-config"
```

This is the contents of the published config file:

```php
return [

    /*
     *  Enable or disable the event listeners.
     */
    'enabled' => env('ECOW_ENABLED', true),

    /*
     * The model used to store saved models.
     */
    'model' => \Inmanturbo\Ecow\Models\SavedModel::class,

    /*
     * After this amount of days, the records in `saved_models` will be deleted
     *
     * This functionality uses Laravel's native pruning feature.
     */
    'prune_after_days' => 365 * 1000000, // wouldn't delete this in a million years

    /*
     * The table name used to store saved models.
     *
     * Changing it is not supported at this time,
     * but it's here for reference and used by the `ecow:migrate` command.
     */
    'saved_models_table' => 'saved_models',

    /*
     * These tables will be created when running the migration.
     *
     * They will be dropped when running `php artisan ecow:migrate --fresh`.
     */
    'migration_tables' => [
        'saved_models',
        'saved_model_snapshots',
    ],

    /*
     * The Models that should be saved by default.
     *
     * You can use '*' to save all models.
     */
    'saved_models' => '*',

    /*
     * The Models that should not be saved by default.
     */
    'unsaved_models' => [],
];
```

## Usage

This packages stores and tracks changes to all your models using creating, updating, and deleting events. This will NOT track any changes made using bulk updates, or changes written directly to the database using the DB facade.

It uses [event sourcing](https://martinfowler.com/eaaDev/EventSourcing.html?ref=bartoszsypytkowski.com) by storing data from native eloquent events and does not require adding any traits to your models!

### Storing arbitrary data

You can store arbitrary data on the model and it will be stored in the model's history, which can be retrieved later using the `Inmanturbo\Ecow\Facades\Ecow` facade.

```php
use Inmanturbo\Ecow\Facades\Ecow;

$model->fakeField = 'this is some fake data';

$model->save();
// no error

$model->fakeField;
// null

$clone = Ecow::retrieveModel(clone $model);

$clone->fakeField;
// 'this is some fake data'
```

It's recommended in most cases you use a clone when retrieving models, rather than modifying the original model, as adding a bunch of arbitrary properties from the history to say, `auth()->user()` at runtime could have unexpected results.

### Snapshotting Models

`Ecow::retrieveModel` loops through all previous versions of the model to build up state. If you have millions of versions for a model this could slow things down a bit. Snapshots set the current state, then changes are tracked from then on.

```php
Ecow::snapshotModel($model);
```

### Querying versions and changes made on a model

You can query all the saved versions of a model using `Inmanturbo\Ecow\Facades\Ecow::savedModelVersions($model)`.

```php
use Inmanturbo\Ecow\Facades\Ecow;

$versions = Ecow::savedModelVersions($model)->latest('model_version')->limit(10)->get();

foreach ($versions as $version) {
    // get the saved models version
    $modelVersion = $version->model_version;

    // make an in memory copy of the model
    $modelCopy = $version->makeRestoredModel();

    // reset the current model's state to this version
    $modelCopy->save();

    //
}
```

### Replaying model history

You can replay the history of all recorded models using `php artisan ecow:replay-models`

```bash
php artisan ecow:replay-models
```

This will truncate all recorded models and replay through all of their built up state using current application logic.

### Excluding models from Ecow listeners

Some models you may not want to be recorded. You can add their class names to the `unsaved_models` array in the `ecow.php` config file.

```bash
php artisan vendor:publish --tag="ecow-config"
```
```php
return [
    /...
    /*
     * The Models that should be saved by default.
     *
     * You can use '*' to save all models.
     */
    'saved_models' => '*',

    /*
     * The Models that should not be saved by default.
     */
    'unsaved_models' => [\App\Models\User::class],
];
```

### Only listening for and recording a few models

You might wish to only record a couple models. You can add their class names to the saved_models array in the ecow.php config file.


```php
return [
    /...
    /*
     * The Models that should be saved by default.
     *
     * You can use '*' to save all models.
     */
    'saved_models' => [\App\Models\Subscription::class],

    /*
     * The Models that should not be saved by default.
     */
    'unsaved_models' => [],
];
```

### Overriding the `modelware` pipelines
This package sends the event data through [pilelines](https://laravel.com/docs/11.x/helpers#pipeline) (similiar to middleware), which iterate through collections of invokable classes, these collections are bound into and resolved from the service container. They can be replaced or overridden in the boot method of a service provider using the following syntax:

```php
app()->bind("ecow.{$event}", fn () => collect($pipes));
```

Where the `{$event}` is a [wildcard event](https://laravel.com/docs/11.x/events#wildcard-event-listeners) for eloquent:

- `ecow.eloquent.creating*` => `eloquent.creating*`
- `ecow.eloquent.updating*` => `eloquent.updating*`
- `ecow.eloquent.deleting*` => `eloquent.deleting*`

#### Example

```php
public function boot() {
    // pipes for all eloquent.creating events
    app()->bind('ecow.eloquent.creating*', fn () => collect($pipes = [
        \App\MyCustom\Invokable::class,
    ));
}
```

This package will send the following data object through your custom pipeline:

```php
$data = (object) [
    'event' => $events,
    'model' => $model,
    'attributes' => $this->getAttributes($model),
    'guid' => $this->getModelGuid($model),
    'key' => $model->uuid ?? ($model->getKey() ?? null),
    'halt' => false,
];
```

### Disabling the Ecow Event listeners

You can disable ecow listeners at runtime with `Ecow::disable()`

```php
use Inmanturbo\Ecow\Facades\Ecow;

Ecow::disable();

User::create([...]); // will not be recorded

Ecow::enable();

User::create([...]); // will be recorded
```

You can disable them globally with `config('ecow.enabled')` or `env('ECOW_ENABLED')`

```php
// ecow.php
return [
    /*
     * Enable or disable the event listeners.
     */
    'enabled' => env('ECOW_ENABLED', true),
...
]
```

### A note on model keys

The practice used here is event sourcing, which is best served by using `uuids`, or `guids`, as the model's id could not otherwise be known or globally identifiable, prior to it being committed to the database. However, for convenience, standard auto-incrementing keys are supported by the package, by backfilling the auto-incremented key on the creating event if there is no `uuid`, after the model is created. This requires the package to create the model itself and halt the creating event by returning `false`. The package will also store a guid property in its own table whenever a model is first created. Otherwise updating stored event history is usually a big no-no and is it's definately not recommended. It is only done by the package on creating/created as a workaround.

Also supported, and perhaps the most preferred is using both a `uuid` and (auto incremented) `id` column on your models' tables. Whenever a column called `uuid` is used, `$model->uuid` will be used by the package instead of `$model->getKey()` for recording model versions.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [inmanturbo](https://github.com/inmanturbo)
- [spatie/laravel-event-sourcing](https://github.com/spatie/laravel-event-sourcing)
- [spatie/laravel-deleted-models](https://github.com/spatie/laravel-deleted-models)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
