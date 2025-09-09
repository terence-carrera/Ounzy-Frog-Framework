<?php

namespace Ounzy\FrogFramework\Core;

class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '#')) continue;
            if (!str_contains($trim, '=')) continue;
            [$key, $value] = explode('=', $trim, 2);
            $key = trim($key);
            $value = trim($value);
            $value = self::stripQuotes($value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    protected static function stripQuotes(string $value): string
    {
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return substr($value, 1, -1);
        }
        return $value;
    }
}
