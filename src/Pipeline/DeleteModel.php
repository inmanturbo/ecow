<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class DeleteModel
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $model = $data->model;

        Ecow::addModelBeingSaved($model);

        $model->delete();

        Ecow::removeModelBeingSaved($model);

        return $next($data);
    }
}
