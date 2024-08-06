<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class CreateModel
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $model = $data->model;

        Ecow::addModelBeingSaved($model);

        $columns = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());

        $savedModel = Ecow::savedModelVersions($model)->first();

        $attributes = $savedModel->values;

        foreach ($attributes as $key => $value) {
            if (! in_array($key, $columns)) {
                unset($attributes[$key]);
            }
        }

        $model->forceFill($attributes);
        $model->save();

        Ecow::snapshotModel($model);

        Ecow::removeModelBeingSaved($model);

        return $next($data);
    }
}
