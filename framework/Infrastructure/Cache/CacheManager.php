<?php

namespace Frog\Infrastructure\Cache;

use RuntimeException;

class CacheManager
{
    private array $stores = [];

    public function __construct(private array $config) {}

    public function store(?string $name = null): CacheInterface
    {
        $name = $name ?? ($this->config['default'] ?? 'file');
        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->makeStore($name);
        }
        return $this->stores[$name];
    }

    private function makeStore(string $name): CacheInterface
    {
        $stores = $this->config['stores'] ?? [];
        $cfg = $stores[$name] ?? null;
        if (!$cfg || !isset($cfg['driver'])) {
            throw new RuntimeException("Cache store '{$name}' not configured");
        }
        return match ($cfg['driver']) {
            'array' => new ArrayCache(),
            'file' => new FileCache($cfg['path'] ?? ''),
            default => throw new RuntimeException("Unsupported cache driver '{$cfg['driver']}'"),
        };
    }
}
