<?php

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->increments('id');
        $table->uuid('uuid')->unique();
        $table->string('name');
        $table->string('nickname')->nullable();
        $table->timestamps();
    });

    Schema::create('users_auto_incremented', function ($table) {
        $table->increments('id');
        $table->string('name');
        $table->string('nickname')->nullable();
        $table->timestamps();
    });

    $this->autoIncrementedModel = new class extends \Illuminate\Database\Eloquent\Model
    {
        protected $guarded = [];
        protected $table = 'users_auto_incremented';
    };

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

it('it will store a copy before a model is created with an auto incremented key', function () {
    $this->autoIncrementedModel->fill(['name' => 'Taylor Otwell']);

    $attributes = $this->autoIncrementedModel->toArray();

    $this->autoIncrementedModel->save();

    $this->assertDatabaseHas('users_auto_incremented', ['name' => 'Taylor Otwell']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->autoIncrementedModel),
        'key' => $this->autoIncrementedModel->getKey(),
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

it('it will store a copy before a model is updated with an auto incremented key', function () {
    $this->autoIncrementedModel->fill(['name' => 'Taylor Otwell']);

    $this->autoIncrementedModel->save();

    $this->autoIncrementedModel->update(['name' => 'Taylor Otwell 2']);

    $this->assertDatabaseHas('users_auto_incremented', ['name' => 'Taylor Otwell 2']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->autoIncrementedModel),
        'key' => $this->autoIncrementedModel->getKey(),
        'model_version' => 2,
        'values' => json_encode($this->autoIncrementedModel->toArray()),
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

it('it will store a copy before a model is deleted with an auto incremented key', function () {
    $this->autoIncrementedModel->fill(['name' => 'Taylor Otwell']);

    $this->autoIncrementedModel->save();

    $this->autoIncrementedModel->delete();

    $this->assertDatabaseMissing('users_auto_incremented', ['name' => 'Taylor Otwell']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->autoIncrementedModel),
        'key' => $this->autoIncrementedModel->getKey(),
        'model_version' => 2,
        'values' => json_encode($this->autoIncrementedModel->toArray()),
    ]);
});

it('can store a null value', function () {
    $this->model->fill(['name' => 'Taylor Otwell', 'nickname' => 'test', 'uuid' => str()->ulid()]);

    $this->model->save();

    $this->model->update(['name' => 'Taylor Otwell 2', 'nickname' => null]);

    $this->assertDatabaseHas('users', ['name' => 'Taylor Otwell 2']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->model),
        'key' => $this->model->uuid,
        'property' => 'nickname',
        'value' => null,
    ]);
});

it('can store a null value with an auto incremented key', function () {
    $this->autoIncrementedModel->fill(['name' => 'Taylor Otwell', 'nickname' => 'test']);

    $this->autoIncrementedModel->save();

    $this->autoIncrementedModel->update(['name' => 'Taylor Otwell 2', 'nickname' => null]);

    $this->assertDatabaseHas('users_auto_incremented', ['name' => 'Taylor Otwell 2']);
    $this->assertDatabaseHas('saved_models', [
        'model' => get_class($this->autoIncrementedModel),
        'key' => $this->autoIncrementedModel->getKey(),
        'property' => 'nickname',
        'value' => null,
    ]);
});

it('will replay back to existing state', function () {
    $this->model->fill(['name' => 'Taylor Otwell', 'uuid' => str()->ulid()]);

    $this->model->save();

    $this->assertDatabaseHas('users', ['name' => 'Taylor Otwell']);

    $this->model->delete();

    $two = $this->model->create(['name' => 'Taylor Otwell 2']);

    $three = $this->model->create(['name' => 'Taylor Otwell 3']);

    $two->update(['name' => 'Taylor Otwell 2 updated']);

    $three->delete();

    $all = $this->model->all();

    $this->model->truncate();

    $this->assertFalse($all == $this->model->all());

    $this->artisan('ecow:replay-models')
        ->expectsConfirmation('Do you want to replay the models? This will delete all the models and replay events!', 'yes')
        ->assertExitCode(0);

    $this->assertNotSame($all, $this->model->all());
    $this->assertTrue($all == $this->model->all());
});
