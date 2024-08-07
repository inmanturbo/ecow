<?php

use Inmanturbo\Ecow\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

uses()
    ->beforeEach(function () {
        // $this->artisan('ecow:migrate')->assertExitCode(0);
    })
    ->in('Feature');
