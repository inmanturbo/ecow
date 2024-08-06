<?php

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;
use Inmanturbo\Ecow\Facades\Ecow;

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

it('can store pending model changes', function () {
    $this->model->name = 'John Doe';
    $this->model->saveQuietly();

    $savedModel = Ecow::modelClass()::create([
        'event' => 'eloquent.creating:user',
        'model_version' => 1,
        'key' => $this->model->uuid ?? $this->model->getKey(),
        'model' => $this->model->getMorphClass(),
        'values' => $this->model->toArray(),
        'property' => 'guid',
        'value' => $this->model->uuid,
    ]);

    $this->model->name = 'Jane Doe';

    $storesUpdatedModels = new \Inmanturbo\Ecow\Pipeline\StoreSavedModels;

    $data = (object) [
        'model' => $this->model,
        'event' => 'eloquent.updating:user',
    ];

    $next = function ($data) {
        return $data;
    };

    $storesUpdatedModels($data, $next);

    $this->assertDatabaseHas('saved_models', [
        'event' => 'eloquent.updating',
        'model_version' => 2,
        'key' => $this->model->uuid,
        'model' => $this->model->getMorphClass(),
        'values' => json_encode($this->model->toArray()),
        'property' => 'name',
        'value' => 'Jane Doe',
    ]);

    $updatesModel = new \Inmanturbo\Ecow\Pipeline\UpdateModel;

    $updatesModel($data, $next);

    $this->assertEquals('Jane Doe', $this->model->fresh()->name);
});
