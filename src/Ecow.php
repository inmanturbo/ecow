<?php

namespace Inmanturbo\Ecow;

use Illuminate\Support\Facades\DB;

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
        return $this->modelClass()::where('key', $model->uuid ?? $model->getKey())
            ->where('model', $model->getMorphClass());
    }

    public function savedModelVersions(mixed $model): \Illuminate\Database\Eloquent\Builder
    {
        return $this->savedModels($model)->orderBy('model_version');
    }

    public function savedModelVersion(mixed $model): int
    {
        return $this->savedModels($model)->latest('model_version')->first()->model_version;
    }

    public function modelVersion($model): int
    {
        return $this->snapshots($model)->latest('model_version')->first()->model_version ?? 0;
    }

    public function snapshots(mixed $model): \Illuminate\Database\Query\Builder
    {
        return DB::table('saved_model_snapshots')
            ->where('model', $model->getMorphClass())
            ->where('key', $model->uuid ?? $model->getKey());
    }

    public function getNextModelVersion(mixed $model): int
    {
        return $this->savedModelVersion($model) + 1 ?? 1;
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

    public function snapshotModel(mixed $model): void
    {
        DB::table('saved_model_snapshots')->insert([
            'model' => $model->getMorphClass(),
            'key' => $model->uuid ?? $model->getKey(),
            'model_version' => Ecow::savedModelVersion($model),
        ]);

    }

    public function retrieveModel(mixed $model): mixed
    {
        $properties = $this->savedModelVersions($model)
            ->where('model_version', '>', $this->modelVersion($model))
            ->get(['property', 'value']);

        foreach ($properties as $version) {
            $model->forceFill([$version->property => $version->value]);
        }

        return $model;
    }
}
