<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('saved_models', function (Blueprint $table) {
            $table->id();
            $table->string('event_version')->default('1.0.0');
            $table->string('event');
            $table->string('model');
            $table->string('key', 40);
            $table->bigInteger('model_version')->default(1);
            $table->string('property');
            $table->longText('value')->nullable();
            $table->json('values');

            $table->timestamps();

            $table->unique(['model', 'model_version', 'key']);
        });

        Schema::create('saved_model_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->string('key', 40);
            $table->bigInteger('model_version')->default(1);
            $table->foreignId('saved_model_id');
            $table->json('values');
        });
    }
};
