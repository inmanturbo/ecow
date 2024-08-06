<?php

namespace Inmanturbo\Ecow\Exceptions;

use Exception;

class InvalidSavedModel extends Exception
{
    public static function create(string $model): self
    {
        return new self("The model `{$model}` is invalid. A valid model must extend the model Inmanturbo\Ecow\Models\SavedModel.");
    }
}
