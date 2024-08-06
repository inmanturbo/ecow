<?php

namespace Inmanturbo\Ecow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string modelClass()
 * @method static Inmanturbo\Ecow\Models\SavedModel newModel()
 * @method static Illuminate\Database\Eloquent\Builder savedModels($model)
 * @method static Illuminate\Database\Eloquent\Builder savedModelVersions($model)
 * @method static Illuminate\Database\Eloquent\Builder savedModelVersion($model)
 * @method static int getNextModelVersion($model)
 * @method static void markReplaying()
 * @method static void markNotReplaying()
 * @method static bool isReplaying()
 * @method static Illuminate\Support\Collection getModelsBeingSaved()
 * @method static void addModelBeingSaved($model)
 * @method static void removeModelBeingSaved($model)
 * @method static void clearModelsBeingSaved()
 *
 * @see \Inmanturbo\Ecow\Ecow
 */
class Ecow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Inmanturbo\Ecow\Ecow::class;
    }
}
