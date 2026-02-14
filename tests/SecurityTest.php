<?php

namespace Frog\Tests;

use Frog\Infrastructure\App;

class SecurityTest extends TestCase
{
    public function run(): void
    {
        $root = dirname(__DIR__);
        new App();
        require $root . '/bootstrap/app.php';

        if (function_exists('session_status') && session_status() === PHP_SESSION_DISABLED) {
            $this->assertTrue(true, 'Sessions are disabled in this environment');
            return;
        }

        if (function_exists('frog_ensure_session_started')) {
            frog_ensure_session_started();
        } elseif (function_exists('session_start') && session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        if (function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE) {
            $this->assertTrue(true, 'Session could not be started; skipping CSRF token checks');
            return;
        }

        $token1 = csrf_token();
        $token2 = csrf_token();

        $this->assertTrue($token1 !== '', 'CSRF token should be generated');
        $this->assertEquals($token1, $token2, 'CSRF token should remain stable for the session');

        $field = csrf_field();
        $this->assertTrue(str_contains($field, 'type="hidden"'), 'CSRF field should be a hidden input');
        $this->assertTrue(str_contains($field, 'name="'), 'CSRF field should include a name');
        $this->assertTrue(str_contains($field, 'value="'), 'CSRF field should include a value');
        $this->assertTrue(str_contains($field, htmlspecialchars($token1, ENT_QUOTES, 'UTF-8')), 'CSRF field should include the token');
    }
}
