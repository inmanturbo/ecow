<?php

namespace Inmanturbo\Ecow\Pipeline;

use Closure;

class BackfillAutoIncrementedKey
{
    /**
     * Invoke the class instance.
     */
    public function __invoke(mixed $data, Closure $next)
    {
        if (isset($data->model->uuid) && $data->model->uuid) {
            return $next($data);
        }

        if ($data->model->getKeyType() === 'string') {
            return $next($data);
        }

        $data->savedModel->forceFill(['key' => $data->model->getKey()]);
        $data->savedModel->save();

        return $next($data);
    }
}
