<?php

namespace Inmanturbo\Ecow\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Event;
use Inmanturbo\Ecow\Facades\Ecow;

class ReplayModelsCommand extends Command
{
    public $signature = 'ecow:replay-models 
        {--force : Run the command without asking for confirmation}
        {--database= : The database connection to use}';

    public $description = 'Replay the models';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Do you want to replay the models? This will delete all the models and replay events!')) {
            return self::SUCCESS;
        }

        Event::listen('ecow.info', fn ($payload) => $this->info($payload['message']));

        if ($this->option('database')) {
            app(DatabaseManager::class)
                ->usingConnection($this->option('database'), fn () => $this->body());

            return self::SUCCESS;
        }

        $this->body();

        return self::SUCCESS;
    }

    protected function body()
    {
        Ecow::replayModels();
    }
}
