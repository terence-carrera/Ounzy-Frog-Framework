<?php

namespace Ounzy\FrogFramework\Http\Middleware;

use Ounzy\FrogFramework\Http\Request;
use Ounzy\FrogFramework\Http\Response;

class BearerTokenMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected ?string $hash = null
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $configured = $this->hash ?? env('API_TOKEN');
        if (!$configured) {
            return (new Response())->status(500)->json(['error' => 'API token not configured']);
        }

        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return (new Response())->status(401)->json(['error' => 'Missing bearer token']);
        }
        $presented = substr($auth, 7);

        $valid = false;
        if (password_get_info($configured)['algo'] !== 0) {
            // Stored is a hash
            $valid = password_verify($presented, $configured);
        } else {
            // Stored is plain token fallback (DEV)
            $valid = hash_equals($configured, $presented);
        }

        if (!$valid) {
            return (new Response())->status(401)->json(['error' => 'Invalid token']);
        }

        return $next($request);
    }
}
