<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class EnsureModelDoesNotAlreadyExist
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $model = $data->model;

        if ($model->where($model->getKeyName(), $model->getKey())->exists()) {
            return;
        }


        return $next($data);
    }
}