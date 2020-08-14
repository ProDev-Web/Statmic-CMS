<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Addon;
use Statamic\Statamic;

class DebugCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:support:details';
    protected $description = 'Outpots details helpful for support requests.';

    public function handle()
    {
        $this->line(sprintf('<info>Statamic</info> %s %s', Statamic::version(), Statamic::pro() ? 'Pro' : 'Solo'));
        $this->line('<info>Laravel</info> '.Application::VERSION);
        $this->line('<info>PHP</info> '.phpversion());
        $this->addons();
    }

    private function addons()
    {
        $addons = Addon::all();
        $addons = collect();

        if ($addons->isEmpty()) {
            return $this->line('No addons installed');
        }

        foreach ($addons as $addon) {
            $this->line(sprintf('<info>%s</info> %s', $addon->package(), $addon->version()));
        }
    }
}
