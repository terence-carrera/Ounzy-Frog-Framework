# Middleware Guide

## What is Middleware?

Middleware are classes that sit in the request pipeline allowing you to transform or short‑circuit a request before it reaches the route handler.

## Creating Middleware

```php
use Ounzy\FrogFramework\Http\Middleware\MiddlewareInterface;
use Ounzy\FrogFramework\Http\Request;
use Ounzy\FrogFramework\Http\Response;

class LogMiddleware implements MiddlewareInterface {
  public function handle(Request $r, callable $next): Response {
    error_log('Incoming: ' . $r->path());
    $res = $next($r);
    error_log('Done: ' . $r->path());
    return $res;
  }
}
```

## Registering Middleware

Global:

```php
$router->middleware([LogMiddleware::class]);
```

Per route:

```php
$router->get('/secure', [SecuredController::class, 'index'], [AuthMiddleware::class, LogMiddleware::class]);
```

## Ordering

Global middleware runs first in the order registered, followed by route middleware.

## Early Response

Return a `Response` before calling `$next($r)` to stop the chain.

## Tips

- Keep middleware small & focused.
- Compose multiple instead of creating monoliths.
- Use constructor DI for services (logger, cache, etc.).
