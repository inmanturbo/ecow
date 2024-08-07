<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;

class Halt
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        return $data->halt ? false : $next($data);
    }
}
