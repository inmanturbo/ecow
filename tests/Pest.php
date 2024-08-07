<?php

use Inmanturbo\Ecow\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

uses()
    ->beforeEach(function () {
        $this->artisan('ecow:migrate', ['--force' => true])->assertExitCode(0);
    })
    ->in('Feature');
