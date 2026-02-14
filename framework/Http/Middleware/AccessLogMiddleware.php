<?php

namespace Frog\Http\Middleware;

use Frog\Http\Request;
use Frog\Http\Response;
use Frog\Infrastructure\App;

class AccessLogMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $time = number_format((microtime(true) - $start) * 1000, 2);
        $routeName = null;
        // Attempt to find matched route name from router (simple linear scan of last match candidate)
        $router = App::getInstance()->router();
        foreach ($router->getRoutes() as $r) {
            if ($r['method'] === $request->method() && preg_match($r['pattern'], $request->path())) {
                $routeName = $r['name'] ?? null;
                break;
            }
        }
        $msg = sprintf('[FROG] %s %s %d %sms%s', $request->method(), $request->path(), method_exists($response, 'getStatus') ? $response->getStatus() : 200, $time, $routeName ? " (route:$routeName)" : '');
        error_log($msg);
        return $response;
    }
}


