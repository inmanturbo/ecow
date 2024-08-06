<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Illuminate\Support\Facades\DB;
use Inmanturbo\Ecow\Facades\Ecow;

class StoreUpdatedModels
{
    /**
     * Invoke the class instance.
     */
    public function __invoke($data, Closure $next)
    {
        $model = $data->model;

        $hiddenAttributes = $model->getHidden();

        /*
         * Avoid changing original instance
         */
        $cloned = clone $model;

        $cloned->makeVisible($hiddenAttributes);

        $attributes = $cloned->attributesToArray();

        DB::transaction(function () use ($model, $attributes, $data) {

            Ecow::snapshotModel($model);

            foreach ($model->getDirty() as $key => $value) {
                $savedModel = Ecow::modelClass()::create([
                    'event' => (string) str()->of($data->event)->before(':'),
                    'model_version' => Ecow::getNextModelVersion($model),
                    'key' => $model->uuid ?? $model->getKey(),
                    'model' => $model->getMorphClass(),
                    'values' => $attributes,
                    'property' => $key,
                    'value' => $value,
                ]);
            }
        });

        return $next($data);
    }
}
