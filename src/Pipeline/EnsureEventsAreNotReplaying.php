<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;
use Inmanturbo\Ecow\Models\SavedModel;

class EnsureEventsAreNotReplaying
{
    /**
     * Create a new class instance.
     */
    public function __invoke($data, Closure $next)
    {

        if ($data->model instanceof (Ecow::modelClass())) {
            return false;
        }

        if (Ecow::isReplaying()) {
            return;
        }

        return $next($data);
    }
}
