<?php

namespace Ounzy\FrogFramework\Routing;

use Ounzy\FrogFramework\Core\App;

/**
 * Static facade for routing similar to Laravel's Route::get()
 */
class Route
{
    protected static ?Router $router = null;

    protected static function router(): Router
    {
        if (!static::$router) {
            $app = App::getInstance();
            static::$router = $app->router();
        }
        return static::$router;
    }

    public static function get(string $uri, callable|array $action, array $middleware = []): Router
    {
        return static::router()->get($uri, $action, $middleware);
    }

    public static function post(string $uri, callable|array $action, array $middleware = []): Router
    {
        return static::router()->post($uri, $action, $middleware);
    }

    public static function group(array $attributes, callable $callback): void
    {
        static::router()->group($attributes, function ($r) use ($callback) {
            $callback($r);
        });
    }

    public static function middleware(array $middleware): Router
    {
        return static::router()->middleware($middleware);
    }

    public static function name(string $name): Router
    {
        return static::router()->name($name);
    }
}
