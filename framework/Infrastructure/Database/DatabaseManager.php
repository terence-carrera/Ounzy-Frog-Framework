<?php

namespace Frog\Infrastructure\Database;

use PDO;
use RuntimeException;

class DatabaseManager
{
    private array $connections = [];

    public function __construct(private array $config) {}

    public function connection(?string $name = null): Connection
    {
        $name = $name ?? ($this->config['default'] ?? 'default');
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }
        return $this->connections[$name];
    }

    public function select(string $sql, array $params = [], ?string $name = null): array
    {
        return $this->connection($name)->select($sql, $params);
    }

    public function statement(string $sql, array $params = [], ?string $name = null): int
    {
        return $this->connection($name)->statement($sql, $params);
    }

    public function insert(string $sql, array $params = [], ?string $name = null): string
    {
        return $this->connection($name)->insert($sql, $params);
    }

    public function update(string $sql, array $params = [], ?string $name = null): int
    {
        return $this->connection($name)->update($sql, $params);
    }

    public function delete(string $sql, array $params = [], ?string $name = null): int
    {
        return $this->connection($name)->delete($sql, $params);
    }

    private function makeConnection(string $name): Connection
    {
        $connections = $this->config['connections'] ?? [];
        $cfg = $connections[$name] ?? null;
        if (!$cfg || !isset($cfg['driver'])) {
            throw new RuntimeException("Database connection '{$name}' not configured");
        }

        $driver = $cfg['driver'];
        $options = $cfg['options'] ?? [];
        $options += [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        return new Connection($this->createPdo($driver, $cfg, $options));
    }

    private function createPdo(string $driver, array $cfg, array $options): PDO
    {
        if ($driver === 'sqlite') {
            $database = $cfg['database'] ?? '';
            if ($database === '') {
                throw new RuntimeException('SQLite database path is required');
            }
            $dsn = 'sqlite:' . $database;
            return new PDO($dsn, null, null, $options);
        }

        $host = $cfg['host'] ?? '127.0.0.1';
        $port = $cfg['port'] ?? null;
        $database = $cfg['database'] ?? '';
        $username = $cfg['username'] ?? '';
        $password = $cfg['password'] ?? '';

        if ($driver === 'mysql') {
            $charset = $cfg['charset'] ?? 'utf8mb4';
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
            return new PDO($dsn, $username, $password, $options);
        }

        if ($driver === 'pgsql') {
            $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
            return new PDO($dsn, $username, $password, $options);
        }

        if ($driver === 'sqlsrv') {
            $instance = $cfg['instance'] ?? '';
            $server = $host;
            if ($instance !== '') {
                $server .= '\\' . $instance;
            }
            if (!empty($port)) {
                $server .= ',' . $port;
            }
            $encrypt = $cfg['encrypt'] ?? false;
            $trustServerCert = $cfg['trust_server_certificate'] ?? false;
            $dsn = "sqlsrv:Server={$server};Database={$database}";
            if ($encrypt) {
                $dsn .= ';Encrypt=1';
            }
            if ($trustServerCert) {
                $dsn .= ';TrustServerCertificate=1';
            }
            return new PDO($dsn, $username, $password, $options);
        }

        throw new RuntimeException("Unsupported database driver '{$driver}'");
    }
}
