<?php

namespace Frog\Http\Middleware;

use Frog\Http\Request;
use Frog\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}

