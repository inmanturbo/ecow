<?php

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->increments('id');
        $table->uuid('uuid')->unique();
        $table->string('name');
        $table->timestamps();
    });

    $this->model = new class extends \Illuminate\Database\Eloquent\Model
    {
        use HasUuids;

        protected $table = 'users';

        protected $guarded = [];

        /**
         * Generate a new UUID for the model.
         */
        public function newUniqueId(): string
        {
            return (string) str()->ulid();
        }

        /**
         * Get the columns that should receive a unique identifier.
         *
         * @return array<int, string>
         */
        public function uniqueIds(): array
        {
            return ['uuid'];
        }
    };
});

it('it will store a copy before a model is created', function () {
    $this->model->fill(['name' => 'Taylor Otwell', 'uuid' => str()->ulid()]);

    $attributes = $this->model->toArray();

    $this->model->save();

    $this->assertDatabaseHas('users', ['name' => 'Taylor Otwell']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->model),
        'key' => $this->model->uuid,
        'model_version' => 1,
        'values' => json_encode($attributes),
    ]);
});

it('it will store a copy before a model is updated', function () {
    $this->model->fill(['name' => 'Taylor Otwell', 'uuid' => str()->ulid()]);

    $this->model->save();

    $this->model->update(['name' => 'Taylor Otwell 2']);

    $this->assertDatabaseHas('users', ['name' => 'Taylor Otwell 2']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->model),
        'key' => $this->model->uuid,
        'model_version' => 2,
        'values' => json_encode($this->model->toArray()),
    ]);
});

it('it will store a copy before a model is deleted', function () {
    $this->model->fill(['name' => 'Taylor Otwell', 'uuid' => str()->ulid()]);

    $this->model->save();

    $this->model->delete();

    $this->assertDatabaseMissing('users', ['name' => 'Taylor Otwell']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->model),
        'key' => $this->model->uuid,
        'model_version' => 2,
        'values' => json_encode($this->model->toArray()),
    ]);
});