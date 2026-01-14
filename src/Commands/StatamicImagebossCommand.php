<?php

namespace Noo\StatamicImageboss\Commands;

use Illuminate\Console\Command;

class StatamicImagebossCommand extends Command
{
    public $signature = 'statamic-imageboss';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
