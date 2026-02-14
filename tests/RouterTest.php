<?php

namespace Frog\Tests;

use Frog\Infrastructure\Routing\Router;
use Frog\Http\Request;
use Frog\Infrastructure\App;

class RouterTest extends TestCase
{
    public function run(): void
    {
        new App();
        $router = new Router();
        $router->get('/hello/{name}', function (Request $r, array $params) {
            return response()->html($params['name']);
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/hello/Frog';
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_FILES = [];
        $request = new \Frog\Http\Request($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES, []);
        $response = $router->dispatch($request);
        $this->assertEquals('Frog', $response->getContent(), 'Route should output parameter');
    }
}


