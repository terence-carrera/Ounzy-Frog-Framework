<?php

namespace Frog\Infrastructure\Cache;

use RuntimeException;

class FileCache implements CacheInterface
{
    public function __construct(private string $path)
    {
        if (!is_dir($this->path) && !@mkdir($this->path, 0777, true)) {
            throw new RuntimeException('Cache directory not writable: ' . $this->path);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $payload = $this->read($key);
        if ($payload === null) return $default;
        return $payload['value'] ?? $default;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $expires = $ttl ? time() + $ttl : null;
        $payload = ['expires' => $expires, 'value' => $value];
        return (bool)file_put_contents($this->pathFor($key), serialize($payload));
    }

    public function delete(string $key): bool
    {
        $file = $this->pathFor($key);
        if (is_file($file)) {
            @unlink($file);
        }
        return true;
    }

    public function clear(): bool
    {
        foreach (glob($this->path . '/*.cache') ?: [] as $file) {
            @unlink($file);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return $this->read($key) !== null;
    }

    private function read(string $key): ?array
    {
        $file = $this->pathFor($key);
        if (!is_file($file)) return null;
        $payload = @unserialize((string)file_get_contents($file));
        if (!is_array($payload)) return null;
        $expires = $payload['expires'] ?? null;
        if ($expires !== null && $expires < time()) {
            $this->delete($key);
            return null;
        }
        return $payload;
    }

    private function pathFor(string $key): string
    {
        $safe = sha1($key);
        return rtrim($this->path, '/\\') . DIRECTORY_SEPARATOR . $safe . '.cache';
    }
}
