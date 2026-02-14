<?php

namespace Frog\Console\Commands;

use Frog\Console\Command;
use Frog\Infrastructure\App;

class Serve extends Command
{
    protected string $signature = 'hop';
    protected string $description = 'Hop into the development server';

    public function handle(array $arguments = []): int
    {
        $host = 'localhost';
        $port = 8000;
        $noColor = false;
        $quiet = false;
        $raw = false;
        $noStdin = false;
        $root = dirname(__DIR__, 3);
        foreach ($arguments as $arg) {
            if (str_starts_with($arg, '--host=')) {
                $host = substr($arg, 7);
            }
            if (str_starts_with($arg, '--port=')) {
                $port = (int)substr($arg, 7);
            }
            if ($arg === '--no-color') {
                $noColor = true;
            }
            if ($arg === '--quiet') {
                $quiet = true;
            }
            if ($arg === '--raw') {
                $raw = true;
            }
            if ($arg === '--no-stdin') {
                $noStdin = true;
            }
        }
        if ($noColor) {
            self::enableColors(false);
        }
        if (!$quiet) {
            $app = new App();
            require $root . '/bootstrap/app.php';

            $label = fn(string $text) => $this->colorize('37', $text);
            $value = fn(string $text) => $this->colorize('37', $text);
            $muted = fn(string $text) => $this->colorize('90', $text);
            $stripAnsi = fn(string $text) => preg_replace('/\x1b\[[0-9;]*m/', '', $text);
            $pad = function (string $text, int $width) use ($stripAnsi): string {
                $len = strlen((string)$stripAnsi($text));
                $pad = max(0, $width - $len);
                return $text . str_repeat(' ', $pad);
            };

            $appName = (string)config('app.name', 'Frog');
            $appVersion = (string)config('app.version', '0.1.0');
            $appEnv = (string)config('app.env', 'production');
            $dbDriver = (string)config('database.default', 'sqlite');
            $keycloakEnabled = (bool)config('keycloak.enabled', false);
            $mailDriver = (string)config('mail.default', 'mail');

            $rows = [
                ['App', $appName . ' v' . $appVersion],
                ['PHP', PHP_VERSION],
                ['Env', $appEnv],
                ['Database', $dbDriver],
                ['Keycloak', $keycloakEnabled ? 'enabled' : 'disabled'],
                ['Mailer', $mailDriver],
                ['Local', 'http://' . $host . ':' . $port],
                ['Network', 'http://' . $host . ':' . $port],
            ];

            $labelWidth = 9;
            $valueWidth = 38;
            $border = '  +' . str_repeat('-', $labelWidth + 2) . '+' . str_repeat('-', $valueWidth + 2) . '+';

            $this->line($this->colorize('37', '  FROG  Development Server'));
            $this->line($muted($border));
            foreach ($rows as [$k, $v]) {
                $kCell = $pad($label($k), $labelWidth);
                $vCell = $pad($value($v), $valueWidth);
                $this->line('  | ' . $kCell . ' | ' . $vCell . ' |');
            }
            $this->line($muted($border));
            $this->line('');
            if ($noStdin) {
                $this->line($muted('  Press Ctrl+C to stop'));
            } else {
                $this->line($muted('  Press Q then Enter to stop'));
            }
            $this->line('');
        }
        if (!$quiet) {
            if (function_exists('ob_get_level')) {
                while (ob_get_level() > 0) {
                    @ob_end_flush();
                }
            }
            if (function_exists('ob_implicit_flush')) {
                @ob_implicit_flush(true);
            }
            if (defined('STDOUT')) {
                @stream_set_write_buffer(STDOUT, 0);
            }
        }
        $stdinAvailable = !$noStdin && defined('STDIN') && is_resource(STDIN);
        if ($stdinAvailable) {
            @stream_set_blocking(STDIN, false);
        }
        $stdinBuffer = '';
        $columns = (int)($_SERVER['COLUMNS'] ?? getenv('COLUMNS') ?: 80);
        putenv('FROG_LOG_COLUMNS=' . $columns);

        $cmd = escapeshellcmd(PHP_BINARY) . sprintf(' -S %s:%d -t public public/index.php', $host, $port);
        $env = array_merge($_SERVER ?? [], $_ENV ?? []);
        $env['FROG_LOG_COLUMNS'] = (string)$columns;
        $env['FROG_LOG_FILE'] = '';
        $descriptors = [
            0 => STDIN,
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = @proc_open($cmd, $descriptors, $pipes, getcwd(), $env);
        if (!is_resource($process)) {
            $this->warn('proc_open unavailable, falling back to passthru');
            passthru($cmd, $exit);
            $this->line('Server stopped.');
            return $exit ?? 0;
        }
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stopRequested = false;
        if (function_exists('sapi_windows_set_ctrl_handler')) {
            sapi_windows_set_ctrl_handler(function () use (&$stopRequested): bool {
                $stopRequested = true;
                return true;
            });
        }
        if (function_exists('pcntl_signal')) {
            if (function_exists('pcntl_async_signals')) {
                pcntl_async_signals(true);
            }
            pcntl_signal(SIGINT, function () use (&$stopRequested): void {
                $stopRequested = true;
            });
        }
        // Poll until user terminates (Ctrl+C) or process exits
        while (true) {
            if ($stopRequested) {
                $code = 0;
                break;
            }
            if ($stdinAvailable) {
                $input = fread(STDIN, 1024);
                if ($input !== false && $input !== '') {
                    $stdinBuffer .= $input;
                    if (str_contains($stdinBuffer, "\n")) {
                        $lines = explode("\n", $stdinBuffer);
                        $stdinBuffer = array_pop($lines);
                        foreach ($lines as $line) {
                            $line = strtolower(trim($line));
                            if ($line === 'q' || $line === 'quit' || $line === 'exit') {
                                $code = 0;
                                break 2;
                            }
                        }
                    }
                }
            }
            $read = [$pipes[1], $pipes[2]];
            $write = null;
            $except = null;
            if (stream_select($read, $write, $except, 0, 200000) !== false) {
                foreach ($read as $stream) {
                    $line = fgets($stream);
                    if ($line === false) continue;
                    $line = rtrim($line, "\r\n");
                    if (str_contains($line, '[FROG]')) {
                        $this->line($line);
                        continue;
                    }
                    if ($raw) {
                        $this->line($line);
                    }
                }
            }
            $status = proc_get_status($process);
            if (!$status['running']) {
                $code = $status['exitcode'];
                break;
            }
        }
        if (isset($status['running']) && $status['running']) {
            @proc_terminate($process);
        }
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        proc_close($process);
        $this->info('Server stopped.');
        if (isset($code)) {
            $ctrlCExitCodes = [-1073741510, 3221225786, 130];
            if (in_array($code, $ctrlCExitCodes, true)) {
                return 0;
            }
        }
        return $code ?? 0;
    }
}

