<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class EnsureEventsAreNotReplaying
{
    /**
     * Create a new class instance.
     */
    public function __invoke($data, Closure $next)
    {

        if (Ecow::isReplaying()) {
            return;
        }

        return $next($data);
    }
}
