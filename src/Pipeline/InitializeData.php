<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class InitializeData
{
    /**
     * Invoke the class instance.
     */
    public function __invoke($data, Closure $next)
    {
        $data->attributes = Ecow::getAttributes($data->model);
        $data->guid = Ecow::getModelGuid($data->model);
        $data->key = $data->model->uuid ?? ($data->model->getKey() ?? null);

        return $next($data);
    }
}
