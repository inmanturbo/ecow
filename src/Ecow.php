<?php

namespace Inmanturbo\Ecow;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inmanturbo\Ecow\Events\FinishedReplayingModels;
use Inmanturbo\Ecow\Events\StartedReplayingModels;
use Inmanturbo\Ecow\Pipeline\BackfillAutoIncrementedKey;
use Inmanturbo\Ecow\Pipeline\CreateModel;
use Inmanturbo\Ecow\Pipeline\CreateSavedModel;
use Inmanturbo\Ecow\Pipeline\EnsureEventsAreNotReplaying;
use Inmanturbo\Ecow\Pipeline\EnsureModelDoesNotAlreadyExist;
use Inmanturbo\Ecow\Pipeline\EnsureModelIsNotBeingSaved;
use Inmanturbo\Ecow\Pipeline\EnsureModelIsNotSavedModel;
use Inmanturbo\Ecow\Pipeline\EnsureModelShouldBeSaved;
use Inmanturbo\Ecow\Pipeline\EnsureSavedModelNeedsBackfill;
use Inmanturbo\Ecow\Pipeline\FillModel;
use Inmanturbo\Ecow\Pipeline\FilterAttributes;
use Inmanturbo\Ecow\Pipeline\Halt;
use Inmanturbo\Ecow\Pipeline\InitializeData;
use Inmanturbo\Ecow\Pipeline\StoreDeletedModel;
use Inmanturbo\Ecow\Pipeline\StoreSavedModels;
use Inmanturbo\Modelware\Facades\Modelware;

class Ecow
{
    public bool $isReplaying = false;

    public array $modelsBeingSaved = [];

    public bool $disabled = false;

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

    public function savedModels(mixed $model): mixed
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
        return $this->savedModelVersion($model) + 1;
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
            ->orderBy('model_version')
            ->first()->value ?? $model->getKey();
    }

    public function retrieveModel(mixed $model): mixed
    {
        $model = clone $model;

        $attributes = ($snapshot = $this->snapshots($model)
            ->where('model_version', $this->modelVersion($model))
            ->first())?->values ?? json_encode([]);

        $model->forceFill(json_decode($attributes, true));

        $properties = $this->savedModelVersions($model)
            ->where('model_version', '>', $snapshot?->model_version ?? 0)
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
        if ($this->isDisabled()) {
            return;
        }

        if (config('ecow.enabled', true) === false) {
            return;
        }

        $this->listenForCreatingEvents();
        $this->listenForUpdatingEvents();
        $this->listenForDeletingEvents();
    }

    public function listenForCreatingEvents(): void
    {
        $this->listen('eloquent.creating*', [
            InitializeData::class,
            EnsureModelShouldBeSaved::class,
            EnsureModelIsNotSavedModel::class,
            EnsureEventsAreNotReplaying::class,
            EnsureModelIsNotBeingSaved::class,
            EnsureModelDoesNotAlreadyExist::class,
            CreateSavedModel::class,
            FilterAttributes::class,
            FillModel::class,
            EnsureSavedModelNeedsBackfill::class,
            CreateModel::class,
            BackfillAutoIncrementedKey::class,
            Halt::class,
        ]);
    }

    public function listenForUpdatingEvents(): void
    {
        $this->listen('eloquent.updating*', [
            InitializeData::class,
            EnsureModelShouldBeSaved::class,
            EnsureModelIsNotSavedModel::class,
            EnsureEventsAreNotReplaying::class,
            EnsureModelIsNotBeingSaved::class,
            StoreSavedModels::class,
            FilterAttributes::class,
            FillModel::class,
        ]);
    }

    public function listenForDeletingEvents(): void
    {
        $this->listen('eloquent.deleting*', [
            InitializeData::class,
            EnsureModelShouldBeSaved::class,
            EnsureModelIsNotSavedModel::class,
            EnsureEventsAreNotReplaying::class,
            EnsureModelIsNotBeingSaved::class,
            StoreDeletedModel::class,
        ]);
    }

    public function listen(string $event, array $pipes): void
    {
        Modelware::add($event, $pipes, prefix: 'ecow');
    }

    public function markReplaying(): void
    {
        Schema::disableForeignKeyConstraints();

        StartedReplayingModels::dispatch();

        $this->isReplaying = true;
    }

    public function markNotReplaying(): void
    {
        Schema::enableForeignKeyConstraints();

        FinishedReplayingModels::dispatch();

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

    public function asReplay(callable $callback): void
    {
        $this->markReplaying();

        $callback();

        $this->markNotReplaying();
    }

    public function replayModels(): void
    {
        $this->asReplay(fn () => $this->replayAllModels());
    }

    protected function replayAllModels(): void
    {
        $modelClasses = $this->modelClass()::distinct('model')
            ->get(['model']);

        foreach ($modelClasses as $modelClass) {
            $model = $modelClass->model;

            $this->info("truncating $model");

            $model::truncate();
        }

        $models = $this->modelClass()::orderBy('created_at')
            ->get();

        foreach ($models as $event) {
            $model = (new $event->model);

            $class = get_class($model);

            if ($event->model_version === 1) {
                $columns = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());

                $attributes = $event->values;

                foreach ($attributes as $key => $value) {
                    if (! in_array($key, $columns)) {
                        unset($attributes[$key]);
                    }
                }

                $model->forceFill($attributes);
                $model->save();

                $this->info("created $class with key $event->key");

                continue;
            }

            $model = $model->where($model->getKeyName(), $event->key)
                ->orWhere('uuid', $event->key)
                ->first();

            if ($event->event === 'eloquent.deleting') {
                $model->delete();

                $this->info("deleted $class with key $event->key");

                continue;
            }

            $model->forceFill([$event->property => $event->value]);
            $model->save();

            $this->info("updated $event->property to $event->value for $class with key $event->key");
        }
    }

    protected function info($message)
    {
        event('ecow.info', ['payload' => ['message' => $message]]);
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function enable(): void
    {
        $this->disabled = false;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function isNotDisabled(): bool
    {
        return ! $this->isDisabled();
    }
}
