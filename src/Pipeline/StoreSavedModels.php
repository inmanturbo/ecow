<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Illuminate\Support\Facades\DB;
use Inmanturbo\Ecow\Facades\Ecow;

class StoreSavedModels
{
    /**
     * Invoke the class instance.
     */
    public function __invoke($data, Closure $next)
    {
        DB::transaction(function () use (&$data) {

            foreach ($data->model->getDirty() as $property => $value) {
                $savedModel = Ecow::modelClass()::create([
                    'event' => (string) str()->of($data->event)->before(':'),
                    'model_version' => Ecow::getNextModelVersion($data->model),
                    'key' => $data->key,
                    'model' => $data->model->getMorphClass(),
                    'values' => $data->attributes,
                    'property' => $property,
                    'value' => $value,
                ]);

                $data->attributes[$savedModel->property] = $savedModel->value;
            }
        });

        return $next($data);
    }
}
