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
 * @method static void rememberModel($model)
 * @method static void forgetModel($model)
 * @method static bool isModelBeingSaved($model)
 * @method static mixed retrieveModel($model)
 * @method static void snapshotModel($model)
 * @method static void snapshot($model)
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
