<?php

use Illuminate\Support\Facades\Artisan;
use Inmanturbo\Ecow\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

uses()
    ->beforeEach(function () {
        Artisan::call('ecow:migrate');
    })
    ->in('Feature');
