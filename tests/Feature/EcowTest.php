<?php

use Inmanturbo\Ecow\Facades\Ecow;

it('can mark replaying', function () {

    expect(Ecow::isReplaying())->toBeFalse();

    Ecow::markReplaying();

    expect(Ecow::isReplaying())->toBeTrue();
});

it('can remember models being saved', function () {
    $model = new class
    {
        public $uuid = '123';

        public function getKey()
        {
            return '123';
        }

        public function getMorphClass()
        {
            return 'test';
        }
    };

    expect(Ecow::getModelsBeingSaved())->toBeEmpty();

    Ecow::addModelBeingSaved($model);

    expect(Ecow::getModelsBeingSaved())->toBe([$model]);

    expect(Ecow::isModelBeingSaved($model))->toBeTrue();

    Ecow::removeModelBeingSaved($model);

    expect(Ecow::getModelsBeingSaved())->toBeEmpty();
});
