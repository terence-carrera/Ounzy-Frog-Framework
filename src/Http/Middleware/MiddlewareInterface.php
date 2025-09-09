<?php

namespace Ounzy\FrogFramework\Http\Middleware;

use Ounzy\FrogFramework\Http\Request;
use Ounzy\FrogFramework\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}
