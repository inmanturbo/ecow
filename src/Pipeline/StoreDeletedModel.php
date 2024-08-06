<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class StoreDeletedModel
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $model = $data->model;

        $model = Ecow::retrieveModel($model);

        $attributes = Ecow::getAttributes($model);

        $key = $model->uuid ?? ($model->getKey() ?? (string) str()->ulid());

        Ecow::modelClass()::create([
            'event' => (string) str()->of($data->event)->before(':'),
            'model_version' => Ecow::getNextModelVersion($model),
            'key' => $key,
            'model' => $model->getMorphClass(),
            'values' => $attributes,
            'property' => 'guid',
            'value' => $model->uuid ?? (string) str()->ulid(),
        ]);

        return $next($data);
    }
}