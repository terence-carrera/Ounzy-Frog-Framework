<?php

namespace Frog\Console\Commands;

use Frog\Console\Application;
use Frog\Console\Command;

class ListCommands extends Command
{
    protected string $signature = 'list';
    protected string $description = 'List available commands';

    public function __construct(protected Application $app) {}

    public function handle(array $arguments = []): int
    {
        $this->line('Available commands:');
        foreach ($this->app->commands() as $cmd) {
            $this->line(sprintf("  %-18s %s", $cmd->signature(), $cmd->description()));
        }
        return 0;
    }
}

