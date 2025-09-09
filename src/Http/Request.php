<?php

namespace Ounzy\FrogFramework\Http;

class Request
{
    public function __construct(
        protected array $get,
        protected array $post,
        protected array $server,
        protected array $cookies,
        protected array $files,
        protected array $inputRaw
    ) {}

    public static function capture(): static
    {
        $raw = file_get_contents('php://input');
        return new static(
            (array)($_GET ?? []),
            (array)($_POST ?? []),
            (array)($_SERVER ?? []),
            (array)($_COOKIE ?? []),
            (array)($_FILES ?? []),
            $raw === false || $raw === '' ? [] : [$raw]
        );
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $qPos = strpos($uri, '?');
        if ($qPos !== false) {
            $uri = substr($uri, 0, $qPos);
        }
        return rtrim($uri, '/') ?: '/';
    }

    public function query(string $key = null, $default = null)
    {
        if ($key === null) return $this->get;
        return $this->get[$key] ?? $default;
    }

    public function input(string $key = null, $default = null)
    {
        if ($key === null) return $this->post;
        return $this->post[$key] ?? $default;
    }

    public function header(string $key, $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }
}
