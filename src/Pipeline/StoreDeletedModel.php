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

        Ecow::modelClass()::create([
            'event' => (string) str()->of($data->event)->before(':'),
            'model_version' => Ecow::getNextModelVersion($data->model),
            'key' => $data->guid,
            'model' => $data->model->getMorphClass(),
            'values' => $data->attributes,
            'property' => 'guid',
            'value' => $data->guid,
        ]);

        return $next($data);
    }
}
