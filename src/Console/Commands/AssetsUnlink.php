<?php

namespace Ounzy\FrogFramework\Console\Commands;

use Ounzy\FrogFramework\Console\Command;

class AssetsUnlink extends Command
{
    protected string $signature = 'assets:unlink';
    protected string $description = 'Remove symlink/junction for resources assets from public directory';

    public function handle(array $arguments = []): int
    {
        $force = in_array('--force', $arguments, true);
        $all = in_array('--all', $arguments, true); // --all implies force
        if ($all) $force = true;
        $root = dirname(__DIR__, 3);
        $public = $root . DIRECTORY_SEPARATOR . 'public';
        $resources = $root . DIRECTORY_SEPARATOR . 'resources';
        $pairs = [
            $resources . DIRECTORY_SEPARATOR . 'images' => $public . DIRECTORY_SEPARATOR . 'images',
            $resources . DIRECTORY_SEPARATOR . 'css' => $public . DIRECTORY_SEPARATOR . 'css',
        ];

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $errors = 0;

        foreach ($pairs as $src => $dest) {
            if (!file_exists($dest) && !is_link($dest)) {
                $this->warn("Missing: $dest (nothing to remove)");
                continue;
            }

            $removed = false;
            // Direct symlink removal works on Unix & Windows symlinks
            if (is_link($dest)) {
                if (@unlink($dest)) {
                    $this->info("Unlinked: $dest");
                    $removed = true;
                } else {
                    $this->error("Failed to unlink: $dest");
                    $errors++;
                    continue;
                }
            } else {
                // Possibly a junction on Windows (is_link false). Detect reparse point.
                if ($isWindows && is_dir($dest)) {
                    $cmd = 'fsutil reparsepoint query "' . $dest . '"';
                    $out = null;
                    $ret = null;
                    @exec($cmd, $out, $ret);
                    if ($ret === 0) { // reparse point
                        if (@rmdir($dest)) {
                            $this->info("Removed junction: $dest");
                            $removed = true;
                        } else {
                            $this->error("Failed to remove junction: $dest");
                            $errors++;
                            continue;
                        }
                    }
                }
            }

            if (!$removed) {
                if (is_dir($dest)) {
                    // Heuristic: if directory structure matches source (contains same filenames) treat as copied asset directory
                    $srcFiles = $this->listFlat($src);
                    $destFiles = $this->listFlat($dest);
                    $isCopiedAsset = !empty($srcFiles) && !empty($destFiles) && count(array_diff($srcFiles, $destFiles)) === 0;
                    if ($isCopiedAsset || $force) {
                        $this->warn(($isCopiedAsset ? 'Removing copied asset directory: ' : 'Force deleting directory: ') . $dest);
                        if (!$this->deleteDirectory($dest)) {
                            $this->error("Failed deleting $dest");
                            $errors++;
                            continue;
                        }
                        $this->info("Deleted: $dest");
                    } else {
                        $this->warn("Skipped regular directory (use --force or --all): $dest");
                    }
                } else {
                    $this->warn("Unknown target type (skipped): $dest");
                }
            }
        }

        return $errors === 0 ? 0 : 1;
    }

    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) return false;
        $items = scandir($dir);
        if ($items === false) return false;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path) && !is_link($path)) {
                if (!$this->deleteDirectory($path)) return false;
            } else {
                if (!@unlink($path)) return false;
            }
        }
        return @rmdir($dir);
    }

    protected function listFlat(string $dir): array
    {
        if (!is_dir($dir)) return [];
        $out = [];
        $it = scandir($dir);
        if ($it === false) return [];
        foreach ($it as $f) {
            if ($f === '.' || $f === '..') continue;
            $out[] = $f;
        }
        sort($out);
        return $out;
    }
}
