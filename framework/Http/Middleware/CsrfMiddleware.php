<?php

namespace Frog\Http\Middleware;

use Frog\Http\Request;
use Frog\Http\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $cfg = config('security.csrf', []);
        if (!(bool)($cfg['enabled'] ?? true)) {
            return $next($request);
        }

        $methods = $cfg['methods'] ?? ['POST', 'PUT', 'PATCH', 'DELETE'];
        if (!in_array($request->method(), $methods, true)) {
            return $next($request);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $this->reject($request, 500, 'Session not started');
        }

        $tokenKey = $cfg['token_key'] ?? '_csrf_token';
        $token = $_SESSION[$tokenKey] ?? null;
        if (!$token) {
            $token = $_SESSION[$tokenKey] = bin2hex(random_bytes(32));
        }

        $headerName = $cfg['header'] ?? 'X-CSRF-TOKEN';
        $presented = $request->header($headerName);
        if (!$presented) {
            $field = $cfg['field'] ?? '_token';
            $presented = $request->input($field) ?? $request->query($field);
        }

        if (!$presented || !hash_equals($token, $presented)) {
            return $this->reject($request, 419, 'Invalid CSRF token');
        }

        return $next($request);
    }

    protected function reject(Request $request, int $status, string $detail): Response
    {
        $accept = $request->header('Accept', '');
        if (str_contains($accept, 'application/json')) {
            return response()->status($status)->json(['error' => $detail]);
        }
        return frog_error_response($status, [
            'message' => 'CSRF Token Mismatch',
            'description' => $detail,
        ]);
    }
}

