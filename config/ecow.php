<?php

return [
    /*
     * The model used to store saved models.
     */
    'model' => \Inmanturbo\Ecow\Models\SavedModel::class,

    /*
     * After this amount of days, the records in `saved_models` will be deleted
     *
     * This functionality uses Laravel's native pruning feature.
     */
    'prune_after_days' => 365 * 1000000, // wouldn't delete this in a million years

    /*
     * The table name used to store saved models.
     */
    'saved_models_table' => 'saved_models',

    /*
     * The Models that should be saved by default.
     */
    'saved_models' => '*',

    /*
     * The Models that should not be saved by default.
     */
    'unsaved_models' => [],
];
