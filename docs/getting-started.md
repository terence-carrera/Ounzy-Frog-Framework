# Getting Started with Frog Framework 🐸

Frog is a minimalist educational PHP micro-framework. This guide shows how to create a new project and run your first page.

## Requirements

- PHP 8.1+ (8.3 recommended)
- Composer

## 1. Create Project

```bash
composer create-project ounzy/frog-framework my-app
cd my-app
```

If `create-project` is not published yet, clone the repository template manually.

## 2. Install Dependencies

```bash
composer install
```

(If you created via create-project, dependencies are already installed.)

## 3. Directory Structure Overview

```text
public/            # Front controller (index.php)
src/
  Core/           # App & Container
  Routing/        # Router
  Http/           # Request/Response & Middleware
  Controllers/    # Controllers
  Services/       # Reusable services
  Views/          # PHP templates
  Support/        # Helpers
  Exceptions/     # Custom exceptions
bootstrap/        # Routes, future boot files
vendor/           # Composer dependencies
frog              # CLI console
```

## 4. Run Dev Server

Use Composer script:

```bash
composer start
```

Or the Frog CLI serve command:

```bash
php frog serve --port=8000
```

Visit: <http://localhost:8000>

## 5. Add a Route

Edit `bootstrap/routes.php`:

```php
$router->get('/ping', fn() => response()->json(['pong' => true]));
```

Visit <http://localhost:8000/ping>

## 6. Create a Controller

Generate with CLI:

```bash
php frog make:controller About
```

Adds `AboutController` with an `index` action.

Register route:

```php
$router->get('/about', [AboutController::class, 'index']);
```

## 7. Views

Create `src/Views/about.php`:

```php
<h1>About Frog</h1>
<p>Lightweight & simple.</p>
```

Controller:

```php
return response()->html(view('about'));
```

## 8. Dependency Injection

Bind services in `bootstrap/routes.php`:

```php
app()->container()->singleton(App\Services\UserRepo::class, App\Services\UserRepo::class);
```

Inject into controllers via constructor type-hints.

## 9. Middleware

Example route-specific middleware:

```php
$router->get('/secure', fn() => response()->html('Secret'), [AuthMiddleware::class]);
```

Global middleware:

```php
$router->middleware([AuthMiddleware::class]);
```

Middleware class:

```php
class AuthMiddleware implements MiddlewareInterface {
  public function handle(Request $r, callable $next): Response {
    if ($r->header('X-API-TOKEN') !== 'secret') {
      return response()->status(401)->json(['error' => 'Unauthorized']);
    }
    return $next($r);
  }
}
```

## 10. CLI Commands

List commands:

```bash
php frog list
```

Generate controller:

```bash
php frog make:controller Admin/User --resource
```

Custom command skeleton: create class under `src/Console/Commands` extending `Command`, then register in `Console\Application`.

## 11. Returning Responses

Methods available on `Response`:

- `status(int)`
- `header(name, value)`
- `html(string)`
- `json(array|object)`

## 12. Helpers

- `response()` create a new Response.
- `view($name, $data=[])` render template
- `app()` access the App singleton
- `container()` access the DI container

## 13. Adding Services

Create `src/Services/GreetingService.php` and bind it:

```php
app()->container()->singleton(GreetingService::class, GreetingService::class);
```

Use in closure route:

```php
$router->get('/hi/{name}', function(Request $r, array $params, GreetingService $svc) {
  return response()->html($svc->greet($params['name']));
});
```

## 14. Error Handling (Basic)

Wrap dispatch in `public/index.php` (already present). Improve by logging or custom error pages later.

## 15. Next Steps

- Add .env loader
- Add logging abstraction
- Add test bootstrap & PHPUnit
- Add caching layer

---

You now have a working Frog project. Explore the other docs for advanced usage.
