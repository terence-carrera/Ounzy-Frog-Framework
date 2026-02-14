<?php

namespace Frog\Infrastructure;

class Config
{
    protected array $items = [];

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $ref = &$this->items;
        foreach ($segments as $seg) {
            if (!isset($ref[$seg]) || !is_array($ref[$seg])) {
                $ref[$seg] = [];
            }
            $ref = &$ref[$seg];
        }
        $ref = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $ref = $this->items;
        foreach ($segments as $seg) {
            if (!array_key_exists($seg, $ref)) return $default;
            $ref = $ref[$seg];
        }
        return $ref;
    }

    public function all(): array
    {
        return $this->items;
    }
}


