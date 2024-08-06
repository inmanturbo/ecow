<?php

namespace Inmanturbo\Ecow;

class Ecow
{
    public bool $isReplaying = false;

    public array $modelsBeingSaved = [];

    public function modelClass(): string
    {
        $modelClass = config('ecow.model');

        if (! is_subclass_of($modelClass, Models\SavedModel::class)) {
            throw Exceptions\InvalidSavedModel::create($modelClass);
        }

        return $modelClass;
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

    public function savedModelVersion(mixed $model): ?Models\SavedModel
    {
        return $this->savedModels($model)->latest('model_version')->first();
    }

    public function getNextModelVersion(mixed $model): int
    {
        return $this->savedModelVersion($model)?->model_version + 1 ?? 1;
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
