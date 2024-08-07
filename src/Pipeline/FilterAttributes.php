<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;
use Inmanturbo\Ecow\Facades\Ecow;

class FilterAttributes
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $model = $data->model;

        $columns = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());

        $data->attributes = array_filter((array) $data->attributes, function ($key) use ($columns) {
            return in_array($key, $columns);
        }, ARRAY_FILTER_USE_KEY);

        return $next($data);
    }
}
