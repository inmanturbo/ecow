<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;

class FilterAttributes
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        $columns = $data->model->getConnection()->getSchemaBuilder()->getColumnListing($data->model->getTable());

        collect($data->model->getFillable())->each(function ($fillable) use (&$columns) {
            if (! in_array($fillable, $columns)) {
                $columns[] = $fillable;
            }
        });

        collect($data->attributes)->each(function ($value, $key) use (&$columns, $data) {
            if (! in_array($key, $columns)) {
                unset($data->attributes[$key]);
                if (isset($data->model->$key)) {
                    unset($data->model->$key);
                }
            }
        });

        return $next($data);
    }
}
