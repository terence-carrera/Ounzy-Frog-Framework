<?php

namespace Frog\Infrastructure\Cache;

class ArrayCache implements CacheInterface
{
    private array $items = [];
    private array $expires = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) return $default;
        return $this->items[$key];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->items[$key] = $value;
        $this->expires[$key] = $ttl ? time() + $ttl : null;
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->items[$key], $this->expires[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->items = [];
        $this->expires = [];
        return true;
    }

    public function has(string $key): bool
    {
        if (!array_key_exists($key, $this->items)) return false;
        $expires = $this->expires[$key] ?? null;
        if ($expires !== null && $expires < time()) {
            $this->delete($key);
            return false;
        }
        return true;
    }
}
