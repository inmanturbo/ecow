<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;

class EnsureModelShouldBeSaved
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        if(isset($data->model->shouldKeep) && $data->model->shouldKeep === true) {
            return $next($data);
        }

        if(isset($data->model->shouldKeep) && $data->model->shouldKeep === false) {
            return;
        }

        if (in_array(get_class($data->model), config('ecow.unsaved_models'))) {
            return;
        }

        if (config('ecow.saved_models') === '*') {
            return $next($data);
        }

        if (! in_array(get_class($data->model), config('ecow.saved_models'))) {
            return;
        }

        return $next($data);
    }
}
