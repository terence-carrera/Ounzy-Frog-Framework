<?php

namespace Frog\Infrastructure\Database;

use PDO;

class Connection
{
    public function __construct(private PDO $pdo) {}

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function statement(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function insert(string $sql, array $params = []): string
    {
        $this->statement($sql, $params);
        return $this->pdo->lastInsertId();
    }

    public function update(string $sql, array $params = []): int
    {
        return $this->statement($sql, $params);
    }

    public function delete(string $sql, array $params = []): int
    {
        return $this->statement($sql, $params);
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
