<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;

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
