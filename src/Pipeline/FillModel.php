<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;

class FillModel
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $data->model->forceFill($data->attributes);

        return $next($data);
    }
}
