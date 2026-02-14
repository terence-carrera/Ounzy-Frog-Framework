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
        $status = method_exists($response, 'getStatus') ? $response->getStatus() : 200;
        $path = $request->path();
        $prefix = '[FROG]';
        $columns = (int)(getenv('FROG_LOG_COLUMNS') ?: $_SERVER['COLUMNS'] ?? getenv('COLUMNS') ?: 80);

        $colorize = function (string $code, string $text): string {
            if (getenv('NO_COLOR') !== false || getenv('FROG_NO_COLOR') !== false || defined('FROG_NO_COLOR')) {
                return $text;
            }
            return "\033[{$code}m{$text}\033[0m";
        };
        $green = fn(string $text) => $colorize('32', $text);
        $blue = fn(string $text) => $colorize('34', $text);

        $statusText = $status >= 400 ? ' (' . $status . ')' : '';
        $routeText = $routeName ? ' (route:' . $routeName . ')' : '';
        $suffixPlain = ' ' . $path . ' ~' . $time . 'ms' . $statusText . $routeText;
        $dotCount = max(3, $columns - strlen($prefix) - strlen($suffixPlain));
        $dots = str_repeat('.', $dotCount);

        $suffix = ' ' . $path . ' ~' . $time . 'ms' . $statusText;
        if ($routeName) {
            $suffix .= ' ' . $blue('(route:' . $routeName . ')');
        }

        $msg = $green($prefix) . $dots . $suffix;
        $logFile = getenv('FROG_LOG_FILE') ?: '';
        if ($logFile !== '') {
            @file_put_contents($logFile, $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        $written = @file_put_contents('php://stdout', $msg . PHP_EOL, FILE_APPEND);
        if ($written === false) {
            error_log($msg);
        }
        return $response;
    }
}


