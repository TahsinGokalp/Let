<?php

namespace TahsinGokalp\Lett\Commands;

use Illuminate\Console\Command;

class LettCommand extends Command
{
    public $signature = 'lett';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
