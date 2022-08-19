<?php

namespace StianScholtz\TabManager\Commands;

use Illuminate\Console\Command;

class TabManagerCommand extends Command
{
    public $signature = 'tab-manager-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
