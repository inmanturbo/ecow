<?php

namespace Inmanturbo\Ecow;

class Ecow {

    public function modelClass() 
    {
        $modelClass = config('ecow.model');

        if (! is_subclass_of($modelClass, Models\SavedModel::class)) {
            throw Exceptions\InvalidSavedModel::create($modelClass);
        }

        return $modelClass;
    }

    public function newModel() 
    {
        $modelClass = $this->modelClass();

        return new $modelClass;
    }
}
