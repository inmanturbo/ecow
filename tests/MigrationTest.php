<?php

// use Illuminate\Support\Facades\Schema;

// use function Pest\Laravel\assertDatabaseHas;
// use function Pest\Laravel\assertDatabaseMissing;
// use function PHPUnit\Framework\assertFalse;
// use function PHPUnit\Framework\assertTrue;

// it('can create the saved_models table', function () {
//     $this->artisan('ecow:migrate')->assertExitCode(0);

//     assertDatabaseHas('migrations', ['migration' => '2024_07_07_131035_create_saved_models_table']);

//     assertTrue(Schema::hasTable('saved_models'));
// });

// it('can run migrations with --fresh option', function () {
//     $this->artisan('ecow:migrate')->assertExitCode(0);
//     $this->artisan('ecow:migrate --fresh')->assertExitCode(0);

//     assertDatabaseHas('migrations', ['migration' => '2024_07_07_131035_create_saved_models_table']);

//     assertTrue(Schema::hasTable('saved_models'));
// });

// it('can run migrations with --wipe option', function () {
//     $this->artisan('ecow:migrate')->assertExitCode(0);
//     $this->artisan('ecow:migrate --wipe')->assertExitCode(0);

//     assertDatabaseMissing('migrations', ['migration' => '2024_07_07_131035_create_saved_models_table']);

//     assertFalse(Schema::hasTable('saved_models'));
// });

// it('can run migrations with --log-only option', function () {
//     $this->artisan('ecow:migrate')->assertExitCode(0);
//     $this->artisan('ecow:migrate --wipe')->assertExitCode(0);
//     $this->artisan('ecow:migrate --log-only')->assertExitCode(0);

//     assertDatabaseHas('migrations', ['migration' => '2024_07_07_131035_create_saved_models_table']);

//     assertFalse(Schema::hasTable('saved_models'));
// });
