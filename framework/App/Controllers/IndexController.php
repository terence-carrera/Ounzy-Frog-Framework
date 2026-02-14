<?php

namespace Frog\App\Controllers;

use Frog\Http\Request;
use Frog\Http\Response;
use Frog\App\Services\{IndexService};

class IndexController
{
    public function __construct(protected IndexService $service) {}

    public function index(Request $request): Response
    {
        return response()->html(view('landing'));
    }
}


