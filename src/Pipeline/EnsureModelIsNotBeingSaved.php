<?php

namespace App;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class EnsureModelIsNotBeingSaved
{
    /**
     * Create a new class instance.
     */
    public function __invoke($data, Closure $next)
    {
        if (Ecow::isModelBeingSaved($data->model)) {
            return;
        }

        return $next($data);
    }
}
