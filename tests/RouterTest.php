<?php

namespace Ounzy\FrogFramework\Tests;

use Ounzy\FrogFramework\Routing\Router;
use Ounzy\FrogFramework\Http\Request;
use Ounzy\FrogFramework\Core\App;

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
        $request = new \Ounzy\FrogFramework\Http\Request($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES, []);
        $response = $router->dispatch($request);
        $this->assertEquals('Frog', $response->getContent(), 'Route should output parameter');
    }
}
