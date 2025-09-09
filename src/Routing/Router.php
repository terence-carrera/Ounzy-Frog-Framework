<?php

namespace Ounzy\FrogFramework\Routing;

use Ounzy\FrogFramework\Http\Request;
use Ounzy\FrogFramework\Http\Response;
use Ounzy\FrogFramework\Core\App;

class Router
{
    protected array $routes = [];
    protected array $globalMiddleware = [];
    protected array $groupStack = [];
    protected array $named = [];

    public function add(string $method, string $pattern, callable|array $handler, array $middleware = []): self
    {
        [$prefix, $groupMw] = $this->currentGroupContext();
        $pattern = $this->normalizePath($prefix . '/' . ltrim($pattern, '/'));
        $route = [
            'method' => strtoupper($method),
            'pattern' => $this->convertPattern($pattern),
            'raw' => $pattern,
            'handler' => $handler,
            'middleware' => array_merge($groupMw, $middleware),
            'name' => null
        ];
        $this->routes[] = $route;
        return $this;
    }

    public function get(string $pattern, callable|array $handler, array $middleware = []): self
    {
        return $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|array $handler, array $middleware = []): self
    {
        return $this->add('POST', $pattern, $handler, $middleware);
    }

    public function middleware(array $middleware): self
    {
        $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        return $this;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }
            if (preg_match($route['pattern'], $request->path(), $matches)) {
                $params = [];
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) $params[$k] = $v;
                }
                return $this->runMiddlewarePipeline($route, $request, $params);
            }
        }
        return frog_error_response(404, ['description' => 'Route ' . $request->path() . ' not found']);
    }

    protected function runMiddlewarePipeline(array $route, Request $request, array $params): Response
    {
        $middlewares = array_merge($this->globalMiddleware, $route['middleware']);
        $index = 0;
        $app = App::getInstance();
        $container = $app->container();
        $pipeline = function (Request $req) use (&$index, $middlewares, $route, $container, $request, $params, &$pipeline) {
            if (isset($middlewares[$index])) {
                $current = $middlewares[$index++];
                // Resolve middleware via container if class-string
                if (is_string($current)) {
                    $current = $container->make($current);
                }
                if (!method_exists($current, 'handle')) {
                    throw new \RuntimeException('Middleware must have handle(Request $request, callable $next)');
                }
                return $current->handle($req, function ($r) use (&$pipeline) {
                    return $pipeline($r);
                });
            }
            return $this->invokeHandler($route['handler'], $request, $params);
        };
        return $pipeline($request);
    }

    protected function invokeHandler(callable|array $handler, Request $request, array $params): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $app = App::getInstance();
            $controller = $app->container()->make($class);
            return $controller->$method($request, $params);
        }
        // If closure needs DI, attempt container call
        if ($handler instanceof \Closure) {
            $app = App::getInstance();
            // Provide multiple possible parameter name keys for request & params
            $result = $app->container()->call($handler, [
                'request' => $request,
                'r' => $request,
                'params' => $params
            ]);
        } else {
            $result = $handler($request, $params);
        }
        if ($result instanceof Response) {
            return $result;
        }
        return response()->html((string)$result);
    }

    protected function convertPattern(string $pattern): string
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_-]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }

    protected function normalizePath(string $path): string
    {
        $path = '/' . trim(preg_replace('#/+#', '/', $path), '/');
        return $path === '' ? '/' : $path;
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = [
            'prefix' => isset($attributes['prefix']) ? '/' . trim($attributes['prefix'], '/') : '',
            'middleware' => $attributes['middleware'] ?? []
        ];
        $callback($this);
        array_pop($this->groupStack);
    }

    protected function currentGroupContext(): array
    {
        $prefix = '';
        $middleware = [];
        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'];
            $middleware = array_merge($middleware, $group['middleware']);
        }
        return [$prefix, $middleware];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function name(string $name): self
    {
        $last = array_key_last($this->routes);
        if ($last === null) throw new \RuntimeException('No route to name');
        $this->routes[$last]['name'] = $name;
        $this->named[$name] = $this->routes[$last];
        return $this;
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->named[$name])) throw new \RuntimeException("Route name '{$name}' not found");
        $raw = $this->named[$name]['raw'];
        foreach ($params as $k => $v) {
            $raw = preg_replace('#\{' . preg_quote($k, '#') . '\}#', urlencode((string)$v), $raw);
        }
        // Remove unresolved params
        $raw = preg_replace('#\{[^}]+\}#', '', $raw);
        return $raw === '' ? '/' : $raw;
    }
}
