<?php

namespace Frog\Console\Commands;

use Frog\Console\Command;

class RouteList extends Command
{
    protected string $signature = 'route:list';
    protected string $description = 'List registered routes';

    public function handle(array $arguments = []): int
    {
        $router = require __DIR__ . '/../../../bootstrap/routes.php';
        $routes = $router->getRoutes();
        $this->line(str_pad('METHOD', 8) . str_pad('URI', 30) . 'HANDLER');
        $this->line(str_repeat('-', 70));
        foreach ($routes as $r) {
            $handler = is_array($r['handler']) ? implode('@', $r['handler']) : 'Closure';
            $this->line(str_pad($r['method'], 8) . str_pad($r['raw'], 30) . $handler);
        }
        return 0;
    }
}

