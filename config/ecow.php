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
];
