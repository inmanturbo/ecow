<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class UpdateModel
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $model = $data->model;

        Ecow::addModelBeingSaved($model);

        $model->forceFill($data->attributes);
        $model->save();

        Ecow::removeModelBeingSaved($model);

        return $next($data);
    }
}
