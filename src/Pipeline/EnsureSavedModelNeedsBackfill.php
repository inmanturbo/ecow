<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class EnsureSavedModelNeedsBackfill
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        if (Ecow::savedModelVersion($data->model) > 0) {
            return;
        }

        return $next($data);
    }
}
