<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;

class CreateModel
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
