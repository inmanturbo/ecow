<?php

namespace App;

use Closure;

class EnsureModelIsNotBeingSaved
{
    /**
     * Create a new class instance.
     */
    public function __invoke($data, Closure $next)
    {
        if (app()->has('updatedModel')) {
            if(app()['updatedModel'] === $data->model) {
                return;
            }
        }

        return $next($data);
    }
}
