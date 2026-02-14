<?php

namespace Frog\Console;

use Frog\Console\Commands\ListCommands;
use Frog\Console\Commands\Serve;
use Frog\Console\Commands\RouteList;
use Frog\Console\Commands\MakeController;
use Frog\Console\Commands\TestRun;
use Frog\Console\Commands\Scaffold;
use Frog\Console\Commands\MakeApiToken;
use Frog\Console\Commands\AssetsLink;
use Frog\Console\Commands\AssetsUnlink;

class Application
{
    /** @var Command[] */
    protected array $commands = [];

    public function __construct()
    {
        $this->register(new ListCommands($this));
        $this->register(new Serve());
        $this->register(new RouteList());
        $this->register(new MakeController());
        $this->register(new TestRun());
        $this->register(new Scaffold());
        $this->register(new MakeApiToken());
        $this->register(new AssetsLink());
        $this->register(new AssetsUnlink());
    }

    public function register(Command $command): void
    {
        $this->commands[$command->signature()] = $command;
    }

    public function commands(): array
    {
        return $this->commands;
    }

    public function run(array $argv): int
    {
        $script = array_shift($argv); // remove script name
        $name = $argv[0] ?? 'list';
        if ($name === 'list') {
            $argv = []; // ensure arguments clean
        } else {
            array_shift($argv); // remove command name
        }
        if (!isset($this->commands[$name])) {
            echo "Command '$name' not found.\n";
            return 1;
        }
        return $this->commands[$name]->handle($argv);
    }
}

