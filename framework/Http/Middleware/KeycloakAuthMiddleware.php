<?php

namespace Frog\Http\Middleware;

use Frog\Infrastructure\Auth\KeycloakVerifier;
use Frog\Infrastructure\Cache\CacheInterface;
use Frog\Infrastructure\Cache\CacheManager;
use Frog\Http\Request;
use Frog\Http\Response;

class KeycloakAuthMiddleware implements MiddlewareInterface
{
    private KeycloakVerifier $verifier;

    public function __construct(?CacheInterface $cache = null)
    {
        $cfg = config('keycloak', []);
        if ($cache === null && container()->has(CacheManager::class)) {
            $cache = container()->make(CacheManager::class)->store();
        }
        $this->verifier = new KeycloakVerifier($cfg, $cache);
    }

    public function handle(Request $request, callable $next): Response
    {
        $cfg = config('keycloak', []);
        if (!(bool)($cfg['enabled'] ?? false)) {
            return $next($request);
        }

        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return $this->reject($request, 'Missing bearer token');
        }

        $token = substr($auth, 7);
        try {
            $this->verifier->verify($token);
        } catch (\Throwable $e) {
            return $this->reject($request, $e->getMessage());
        }

        return $next($request);
    }

    private function reject(Request $request, string $detail): Response
    {
        $accept = $request->header('Accept', '');
        if (str_contains($accept, 'application/json')) {
            return response()->status(401)->json(['error' => 'Unauthorized', 'detail' => $detail]);
        }
        return frog_error_response(401, ['description' => $detail]);
    }
}
