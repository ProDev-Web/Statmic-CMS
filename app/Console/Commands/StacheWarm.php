<?php

namespace Statamic\Console\Commands;

use Statamic\API\Stache;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class StacheWarm extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:stache:warm';
    protected $description = 'Build the "Stache" cache';

    public function handle()
    {
        $this->line('Please wait. This may take a while if you have a lot of content.');

        Stache::warm();

        $this->info('You have poured oil over the Stache and polished it until it shines. It is warm and ready.');
    }
}
