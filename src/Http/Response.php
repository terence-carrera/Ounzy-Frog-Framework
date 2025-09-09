<?php

namespace Ounzy\FrogFramework\Http;

class Response
{
    protected int $status = 200;
    protected array $headers = [];
    protected string $content = '';

    public function status(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(array|object $data): static
    {
        $this->header('Content-Type', 'application/json; charset=utf-8');
        $this->content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return $this;
    }

    public function html(string $html): static
    {
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->content = $html;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
        echo $this->content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
