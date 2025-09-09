<?php

namespace Ounzy\FrogFramework\Http\Middleware;

use Ounzy\FrogFramework\Http\Request;
use Ounzy\FrogFramework\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Example: simple header token check
        $token = $request->header('X-API-TOKEN');
        if ($token !== 'secret') {
            $accept = $request->header('Accept', '');
            if (str_contains($accept, 'text/html')) {
                return frog_error_response(401);
            }
            return (new Response())->status(401)->json(['error' => 'Unauthorized']);
        }
        return $next($request);
    }
}
