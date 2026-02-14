<?php

namespace Frog\Tests;

abstract class TestCase
{
    protected int $assertions = 0;

    protected function assertTrue(bool $cond, string $message = ''): void
    {
        $this->assertions++;
        if (!$cond) throw new \AssertionError($message ?: 'Failed asserting that condition is true');
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;
        if ($expected != $actual) throw new \AssertionError($message ?: "Expected '" . var_export($expected, true) . "' got '" . var_export($actual, true) . "'");
    }

    public function getAssertionCount(): int
    {
        return $this->assertions;
    }
}

