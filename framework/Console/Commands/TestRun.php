<?php

namespace Frog\Console\Commands;

use Frog\Console\Command;

class TestRun extends Command
{
    protected string $signature = 'test';
    protected string $description = 'Run framework test suite (lightweight)';

    public function handle(array $arguments = []): int
    {
        $dir = __DIR__ . '/../../../tests';
        if (!is_dir($dir)) {
            $this->error('No tests directory');
            return 1;
        }
        $files = glob($dir . '/*Test.php');
        $total = 0;
        $passed = 0;
        $assertions = 0;
        foreach ($files as $file) {
            require_once $file;
            $class = basename($file, '.php');
            $fqcn = 'Frog\\Tests\\' . $class;
            if (class_exists($fqcn)) {
                $class = $fqcn;
            } elseif (!class_exists($class)) {
                continue;
            }
            $total++;
            try {
                $test = new $class();
                $test->run();
                $assertions += $test->getAssertionCount();
                $this->info("PASS: $class");
                $passed++;
            } catch (\Throwable $e) {
                $this->error("FAIL: $class - " . $e->getMessage());
            }
        }
        $this->line("Summary: $passed/$total tests passed, $assertions assertions");
        return $passed === $total ? 0 : 1;
    }
}

