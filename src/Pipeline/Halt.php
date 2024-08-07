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
        $data->halt = true;

        return false;
    }
}
