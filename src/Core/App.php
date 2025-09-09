<?php

namespace Ounzy\FrogFramework\Core;

use Ounzy\FrogFramework\Routing\Router;
use Ounzy\FrogFramework\Core\Container;

class App
{
    protected static ?App $instance = null;
    protected Router $router;
    protected Container $container;

    public function __construct(?Router $router = null, ?Container $container = null)
    {
        $this->router = $router ?? new Router();
        $this->container = $container ?? new Container();
        // register core singletons
        $this->container->instance(App::class, $this);
        $this->container->instance(Router::class, $this->router);
        static::$instance = $this;
    }

    public static function getInstance(): App
    {
        if (!static::$instance) {
            throw new \RuntimeException('App has not been initialised');
        }
        return static::$instance;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function container(): Container
    {
        return $this->container;
    }
}
