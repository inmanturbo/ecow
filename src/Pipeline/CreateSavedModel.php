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
        $data->attributes = ($data->savedModel = Ecow::modelClass()::create([
            'event' => (string) str()->of($data->event)->before(':'),
            'model_version' => 1,
            'key' => $data->guid,
            'model' => $data->model->getMorphClass(),
            'values' => $data->attributes,
            'property' => 'guid',
            'value' => $data->guid,
        ]))->values;

        return $next($data);
    }
}
