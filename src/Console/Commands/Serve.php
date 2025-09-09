<?php

namespace Ounzy\FrogFramework\Console\Commands;

use Ounzy\FrogFramework\Console\Command;

class Serve extends Command
{
    protected string $signature = 'hop';
    protected string $description = 'Hop into the development server';

    public function handle(array $arguments = []): int
    {
        $host = 'localhost';
        $port = 8000;
        $noColor = false;
        $verbose = false;
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
            if ($arg === '--verbose') {
                $verbose = true;
            }
        }
        if ($noColor) {
            self::enableColors(false);
        }
        $this->info("Starting server on http://$host:$port ... (Ctrl+C to stop)");
        $cmd = escapeshellcmd(PHP_BINARY) . sprintf(' -S %s:%d -t public public/index.php', $host, $port);
        if (!$verbose) {
            // Redirect output to NUL (Windows) to suppress built-in server logs
            $cmd .= ' > NUL 2>&1';
        }
        $descriptors = [STDIN, STDOUT, STDERR];
        $process = @proc_open($cmd, $descriptors, $pipes, getcwd());
        if (!is_resource($process)) {
            $this->warn('proc_open unavailable, falling back to passthru');
            passthru($cmd, $exit);
            $this->line('Server stopped.');
            return $exit ?? 0;
        }
        // Poll until user terminates (Ctrl+C) or process exits
        while (true) {
            $status = proc_get_status($process);
            if (!$status['running']) {
                $code = $status['exitcode'];
                break;
            }
            usleep(200000); // 200ms
        }
        proc_close($process);
        $this->info('Server stopped.');
        return $code ?? 0;
    }
}
