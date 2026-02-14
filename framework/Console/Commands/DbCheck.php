<?php

namespace Frog\Console\Commands;

use Frog\Console\Command;
use Frog\Infrastructure\App;
use Frog\Infrastructure\Database\DatabaseManager;

class DbCheck extends Command
{
    protected string $signature = 'db:check';
    protected string $description = 'Check database connection health';

    public function handle(array $arguments = []): int
    {
        $root = dirname(__DIR__, 3);
        $app = new App();
        require $root . '/bootstrap/app.php';

        $connection = null;
        foreach ($arguments as $arg) {
            if (str_starts_with($arg, '--connection=')) {
                $connection = substr($arg, 13);
                continue;
            }
            if ($connection === null && $arg !== '') {
                $connection = $arg;
            }
        }
        $connection = $connection ?: (string)config('database.default', 'sqlite');
        $databaseConfig = (array)config('database', []);
        $connections = (array)($databaseConfig['connections'] ?? []);
        $cfg = $connections[$connection] ?? null;

        if (!$cfg || !isset($cfg['driver'])) {
            $this->error("Connection '{$connection}' is not configured");
            return 1;
        }

        $driver = (string)$cfg['driver'];
        if ($driver === 'sqlsrv') {
            $missing = [];
            if (!extension_loaded('pdo_sqlsrv')) {
                $missing[] = 'pdo_sqlsrv';
            }
            if (!extension_loaded('sqlsrv')) {
                $missing[] = 'sqlsrv';
            }
            if ($missing) {
                $this->error('Missing PHP extension(s): ' . implode(', ', $missing));
                return 1;
            }
        }

        try {
            /** @var DatabaseManager $db */
            $db = container()->make(DatabaseManager::class);
            $db->connection($connection)->select('SELECT 1 AS ok');
            $this->info("Database connection '{$connection}' OK");
            return 0;
        } catch (\Throwable $e) {
            $this->error("Database connection '{$connection}' failed: " . $e->getMessage());
            return 1;
        }
    }
}
