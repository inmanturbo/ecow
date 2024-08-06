<?php

namespace Inmanturbo\Ecow\Exceptions;

use Exception;
use Inmanturbo\Ecow\Models\SavedModel;

class CouldNotRestoreModel extends Exception
{
    public static function make(SavedModel $model, Exception $exception)
    {
        return new self("Could not restore saved model id `{$model->id}` because: {$exception->getMessage()}", previous: $exception);
    }
}
