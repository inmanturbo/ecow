<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class CreateSavedModel
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $model = $data->model;

        $attributes = Ecow::getAttributes($model);

        $key = $model->uuid ?? ($model->getKey() ?? (string) str()->ulid());

        Ecow::modelClass()::create([
            'event' => (string) str()->of($data->event)->before(':'),
            'model_version' => 1,
            'key' => $key,
            'model' => $model->getMorphClass(),
            'values' => $attributes,
            'property' => 'guid',
            'value' => $model->uuid ?? (string) str()->ulid(),
        ]);

        return $next($data);
    }
}