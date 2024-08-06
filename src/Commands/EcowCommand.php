<?php

namespace Inmanturbo\Ecow\Commands;

use Illuminate\Console\Command;

class EcowCommand extends Command
{
    public $signature = 'ecow';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
