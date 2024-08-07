<?php

namespace Inmanturbo\Ecow;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Pipeline;
use Inmanturbo\Ecow\Pipeline\BackfillAutoIncrementedKey;
use Inmanturbo\Ecow\Pipeline\CreateModel;
use Inmanturbo\Ecow\Pipeline\CreateSavedModel;
use Inmanturbo\Ecow\Pipeline\DeleteModel;
use Inmanturbo\Ecow\Pipeline\EnsureEventsAreNotReplaying;
use Inmanturbo\Ecow\Pipeline\EnsureModelDoesNotAlreadyExist;
use Inmanturbo\Ecow\Pipeline\EnsureModelIsNotBeingSaved;
use Inmanturbo\Ecow\Pipeline\EnsureModelIsNotSavedModel;
use Inmanturbo\Ecow\Pipeline\EnsureModelShouldBeSaved;
use Inmanturbo\Ecow\Pipeline\FilterAttributes;
use Inmanturbo\Ecow\Pipeline\StoreDeletedModel;
use Inmanturbo\Ecow\Pipeline\StoreSavedModels;
use Inmanturbo\Ecow\Pipeline\UpdateModel;

class Ecow
{
    public bool $isReplaying = false;

    public array $modelsBeingSaved = [];

    public function modelClass(): string
    {
        $modelClass = config('ecow.model');

        if ($modelClass === Models\SavedModel::class || is_subclass_of($modelClass, Models\SavedModel::class)) {
            return $modelClass;
        }

        throw Exceptions\InvalidSavedModel::create($modelClass);
    }

    public function newModel(): Models\SavedModel
    {
        $modelClass = $this->modelClass();

        return new $modelClass;
    }

    public function savedModels(mixed $model): \Illuminate\Database\Eloquent\Builder
    {
        return $this->modelClass()::where('model', $model->getMorphClass())
            ->where('key', $model->uuid ?? $model->getKey());
    }

    public function savedModelVersions(mixed $model): mixed
    {
        return $this->savedModels($model)->orderBy('model_version');
    }

    public function savedModelVersion(mixed $model): int
    {
        return $this->savedModels($model)->latest('model_version')->first()?->model_version ?? 0;
    }

    public function modelVersion($model): int
    {
        return $this->savedModelVersion($model);
    }

    public function snapshots(mixed $model): mixed
    {
        return DB::table('saved_model_snapshots')
            ->where('model', $model->getMorphClass())
            ->where('key', $model->uuid ?? $model->getKey());
    }

    public function getNextModelVersion(mixed $model): int
    {
        return $this->savedModelVersion($model) + 1 ?? 1;
    }

    public function snapshotModel(mixed $model): void
    {
        $attributes = $this->getAttributes($model);

        $savedModelVersions = $this->savedModelVersions($model);

        $latestVersion = $savedModelVersions->latest('model_version')->first();

        foreach ($savedModelVersions->get() as $version) {
            $attributes[$version->property] = $version->value;
        }

        $attributes['saved_model_id'] = $latestVersion->id;

        DB::table('saved_model_snapshots')->insert([
            'model' => $model->getMorphClass(),
            'key' => $model->uuid ?? $model->getKey(),
            'model_version' => Ecow::savedModelVersion($model),
            'values' => json_encode($attributes, JSON_THROW_ON_ERROR),
            'saved_model_id' => $latestVersion->id,
        ]);
    }

    public function getModelGuid(mixed $model): string
    {
        if (! $model->getKey()) {
            return $model->uuid ?? (string) str()->ulid();
        }

        return $model->uuid ?? $this->modelClass()::where('model', $model->getMorphClass())
            ->where('key', $model->getKey())
            ->where('property', 'guid')
            ->first()->value ?? (string) str()->ulid();
    }

    public function retrieveModel(mixed $model): mixed
    {
        $attributes = $this->snapshots($model)
            ->where('model_version', $this->modelVersion($model))
            ->first()->values ?? json_encode([]);

        $model->forceFill(json_decode($attributes, true));

        $properties = $this->savedModelVersions($model)
            ->where('model_version', '>', $this->modelVersion($model))
            ->get(['property', 'value']);

        foreach ($properties as $version) {
            $model->forceFill([$version->property => $version->value]);
        }

        return $model;
    }

    public function getAttributes(mixed $model): array
    {
        $hiddenAttributes = $model->getHidden();

        /*
         * Avoid changing original instance
         */
        $cloned = clone $model;

        $cloned->makeVisible($hiddenAttributes);

        $attributes = $cloned->attributesToArray();

        return $attributes;
    }

    public function bootListeners(): void
    {
        $this->listenForCreatingEvents();
        $this->listenForUpdatingEvents();
        $this->listenForDeletingEvents();
    }

    public function listenForCreatingEvents(): void
    {
        $this->listen('eloquent.creating*', [
            EnsureModelShouldBeSaved::class,
            EnsureModelIsNotSavedModel::class,
            EnsureEventsAreNotReplaying::class,
            EnsureModelIsNotBeingSaved::class,
            EnsureModelDoesNotAlreadyExist::class,
            CreateSavedModel::class,
            FilterAttributes::class,
            CreateModel::class,
            BackfillAutoIncrementedKey::class,
        ]);
    }

    public function listenForUpdatingEvents(): void
    {
        $this->listen('eloquent.updating*', [
            EnsureModelShouldBeSaved::class,
            EnsureModelIsNotSavedModel::class,
            EnsureEventsAreNotReplaying::class,
            EnsureModelIsNotBeingSaved::class,
            StoreSavedModels::class,
            FilterAttributes::class,
            UpdateModel::class,
        ]);
    }

    public function listenForDeletingEvents(): void
    {
        $this->listen('eloquent.deleting*', [
            EnsureModelShouldBeSaved::class,
            EnsureModelIsNotSavedModel::class,
            EnsureEventsAreNotReplaying::class,
            EnsureModelIsNotBeingSaved::class,
            StoreDeletedModel::class,
            DeleteModel::class,
        ]);
    }

    public function listen(string $event, array $pipes): void
    {
        app()->bind("ecow.{$event}", fn () => Collection::make($pipes));

        Event::listen($event, function (string $events, array $payload) use ($event) {
            return $this->eventPipeline($event, $payload, $events);
        });
    }

    protected function eventPipeline(string $event, array $payload, $events): mixed
    {
        $model = $payload[0];

        $data = (object) [
            'event' => $events,
            'model' => $model,
            'attributes' => $this->getAttributes($model),
            'guid' => $this->getModelGuid($model),
            'key' => $model->uuid ?? ($model->getKey() ?? null),
        ];

        $pipeline = Pipeline::send($data)
            ->through(app("ecow.{$event}")->toArray())
            ->then(fn ($data) => false);

        return $pipeline;
    }

    public function markReplaying(): void
    {
        $this->isReplaying = true;
    }

    public function markNotReplaying(): void
    {
        $this->isReplaying = false;
    }

    public function isReplaying(): bool
    {
        return $this->isReplaying;
    }

    public function addModelBeingSaved(mixed $model): void
    {
        $this->rememberModel($model);
    }

    public function removeModelBeingSaved(mixed $model): void
    {
        $this->forgetModel($model);
    }

    public function rememberModel(mixed $model): void
    {
        $this->modelsBeingSaved[] = $model;
    }

    public function forgetModel(mixed $model): void
    {
        $this->modelsBeingSaved = array_filter($this->modelsBeingSaved, fn ($m) => $m !== $model);
    }

    public function isModelBeingSaved($model = null): bool
    {
        if (! $model) {
            return false;
        }

        return in_array($model, $this->modelsBeingSaved);
    }

    public function getModelsBeingSaved(): array
    {
        return $this->modelsBeingSaved;
    }

    public function clearModelsBeingSaved(): void
    {
        $this->modelsBeingSaved = [];
    }
}
