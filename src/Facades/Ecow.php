<?php

namespace Inmanturbo\Ecow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Inmanturbo\Ecow\Ecow
 */
class Ecow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Inmanturbo\Ecow\Ecow::class;
    }
}
