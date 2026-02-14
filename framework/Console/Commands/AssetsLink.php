<?php

namespace Frog\Console\Commands;

use Frog\Console\Command;

class AssetsLink extends Command
{
    protected string $signature = 'assets:link';
    protected string $description = 'Create symlink (or junction on Windows) for resources assets into public directory';

    public function handle(array $arguments = []): int
    {
        $root = dirname(__DIR__, 3); // up from framework/Console/Commands
        $resources = $root . DIRECTORY_SEPARATOR . 'resources';
        $public = $root . DIRECTORY_SEPARATOR . 'public';
        $pairs = [
            $resources . DIRECTORY_SEPARATOR . 'images' => $public . DIRECTORY_SEPARATOR . 'images',
            $resources . DIRECTORY_SEPARATOR . 'css' => $public . DIRECTORY_SEPARATOR . 'css',
        ];

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $errors = 0;

        foreach ($pairs as $src => $dest) {
            if (!is_dir($src)) {
                $this->warn("Skip: source missing $src");
                continue;
            }
            if (is_link($dest) || file_exists($dest)) {
                $this->info("Exists: $dest");
                continue;
            }
            if ($isWindows) {
                // Use mklink /J for directory junction (no admin required like /D sometimes)
                $cmd = sprintf('cmd /C mklink /J "%s" "%s"', $dest, $src);
            } else {
                $cmd = sprintf('ln -s "%s" "%s"', $src, $dest);
            }
            $this->line("Linking: $dest -> $src");
            $out = null;
            $ret = null;
            @exec($cmd, $out, $ret);
            if ($ret !== 0) {
                $this->warn("Link failed (code $ret). Falling back to copy...");
                if (!$this->recursiveCopy($src, $dest)) {
                    $this->error("Failed copying assets to $dest");
                    $errors++;
                } else {
                    $this->info('Copied');
                }
            } else {
                $this->info('Created');
            }
        }

        return $errors === 0 ? 0 : 1;
    }

    protected function recursiveCopy(string $src, string $dest): bool
    {
        if (!is_dir($src)) return false;
        if (!is_dir($dest) && !@mkdir($dest, 0777, true)) return false;
        $items = scandir($src);
        if ($items === false) return false;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $s = $src . DIRECTORY_SEPARATOR . $item;
            $d = $dest . DIRECTORY_SEPARATOR . $item;
            if (is_dir($s)) {
                if (!$this->recursiveCopy($s, $d)) return false;
            } else {
                if (!@copy($s, $d)) return false;
            }
        }
        return true;
    }
}

