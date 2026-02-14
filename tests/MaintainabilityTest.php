<?php

namespace Frog\Tests;

class MaintainabilityTest extends TestCase
{
    public function run(): void
    {
        $root = dirname(__DIR__);
        $dbConfig = file_get_contents($root . '/config/database.php') ?: '';
        $dbManager = file_get_contents($root . '/framework/Infrastructure/Database/DatabaseManager.php') ?: '';

        $this->assertTrue(
            str_contains($dbConfig, "'sqlsrv'") && str_contains($dbConfig, "'driver' => 'sqlsrv'"),
            'Database config should include a sqlsrv connection'
        );
        $this->assertTrue(
            str_contains($dbManager, "if (\$driver === 'sqlsrv')"),
            'DatabaseManager should support the sqlsrv driver'
        );
    }
}
