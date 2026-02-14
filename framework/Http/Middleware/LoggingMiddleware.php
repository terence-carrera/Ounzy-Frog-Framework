<?php

namespace Frog\Http\Middleware;

use Frog\Http\Request;
use Frog\Http\Response;

class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(protected ?string $channel = null) {}

    public function handle(Request $request, callable $next): Response
    {
        $start = microtime(true);
        $res = $next($request);
        $time = number_format((microtime(true) - $start) * 1000, 2);
        error_log(sprintf('[Frog] %s %s %d %sms', $request->method(), $request->path(), 200, $time));
        return $res;
    }
}

