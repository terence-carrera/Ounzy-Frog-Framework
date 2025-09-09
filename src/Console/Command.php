<?php

namespace Ounzy\FrogFramework\Console;

abstract class Command
{
    protected string $signature; // e.g. route:list
    protected string $description = '';
    protected static ?bool $colorEnabled = null;

    public static function enableColors(bool $enable): void
    {
        static::$colorEnabled = $enable;
    }

    protected static function colors(): bool
    {
        if (static::$colorEnabled !== null) return static::$colorEnabled;
        // Auto-detect
        if (getenv('NO_COLOR') !== false || getenv('FROG_NO_COLOR') !== false || defined('FROG_NO_COLOR')) {
            return static::$colorEnabled = false;
        }
        $isWindows = DIRECTORY_SEPARATOR === '\\';
        if ($isWindows) {
            // Try enable VT100 support if possible, else disable
            if (function_exists('sapi_windows_vt100_support')) {
                @sapi_windows_vt100_support(STDOUT, true);
            }
        }
        return static::$colorEnabled = function_exists('stream_isatty') ? @stream_isatty(STDOUT) : true;
    }

    protected function colorize(string $code, string $text): string
    {
        if (!self::colors()) return $text;
        return "\033[{$code}m{$text}\033[0m";
    }

    public function signature(): string
    {
        return $this->signature;
    }

    public function description(): string
    {
        return $this->description;
    }

    abstract public function handle(array $arguments = []): int;

    protected function line(string $text = ''): void
    {
        echo $text . PHP_EOL;
    }

    protected function info(string $text): void
    {
        $this->line($this->colorize('32', $text));
    }
    protected function error(string $text): void
    {
        $this->line($this->colorize('31', $text));
    }
    protected function warn(string $text): void
    {
        $this->line($this->colorize('33', $text));
    }
}
