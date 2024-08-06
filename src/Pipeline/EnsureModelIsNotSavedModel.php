<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class EnsureModelIsNotSavedModel
{
    /**
     * Create a new class instance.
     */
    public function __invoke($data, Closure $next)
    {

        if ($data->model instanceof (Ecow::modelClass())) {
            return;
        }

        return $next($data);
    }
}
