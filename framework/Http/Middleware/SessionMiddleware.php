<?php

namespace Frog\Http\Middleware;

use Frog\Http\Request;
use Frog\Http\Response;

class SessionMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
            $cfg = config('session', []);
            $name = $cfg['name'] ?? 'FROGSESSID';
            if ($name && session_name() !== $name) {
                session_name($name);
            }

            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            $lifetimeMinutes = (int)($cfg['lifetime'] ?? 120);
            $params = [
                'lifetime' => $lifetimeMinutes * 60,
                'path' => $cfg['path'] ?? '/',
                'domain' => $cfg['domain'] ?? '',
                'secure' => (bool)($cfg['secure'] ?? false),
                'httponly' => (bool)($cfg['httponly'] ?? true),
                'samesite' => $cfg['samesite'] ?? 'Lax',
            ];
            session_set_cookie_params($params);
            if (!headers_sent()) {
                @session_start();
            }
        }

        return $next($request);
    }
}

