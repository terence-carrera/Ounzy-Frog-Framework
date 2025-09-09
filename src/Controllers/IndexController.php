<?php

namespace Ounzy\FrogFramework\Controllers;

use Ounzy\FrogFramework\Http\Request;
use Ounzy\FrogFramework\Http\Response;
use Ounzy\FrogFramework\Services\{IndexService};

class IndexController
{
    public function __construct(protected IndexService $service) {}

    public function index(Request $request): Response
    {
        return response()->html(view('landing'));
    }
}
