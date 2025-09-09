<?php

namespace Ounzy\FrogFramework\Console;

use Ounzy\FrogFramework\Console\Commands\ListCommands;
use Ounzy\FrogFramework\Console\Commands\Serve;
use Ounzy\FrogFramework\Console\Commands\RouteList;
use Ounzy\FrogFramework\Console\Commands\MakeController;
use Ounzy\FrogFramework\Console\Commands\TestRun;
use Ounzy\FrogFramework\Console\Commands\Scaffold;
use Ounzy\FrogFramework\Console\Commands\MakeApiToken;
use Ounzy\FrogFramework\Console\Commands\AssetsLink;
use Ounzy\FrogFramework\Console\Commands\AssetsUnlink;

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
