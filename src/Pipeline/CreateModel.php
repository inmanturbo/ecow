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
        Ecow::addModelBeingSaved($data->model);

        $data->model->save();
        $data->halt = true;

        Ecow::removeModelBeingSaved($data->model);

        return $next($data);
    }
}
