<?php

namespace Frog\App\Controllers;

use Frog\App\Services\BlogService;
use Frog\Http\Request;
use Frog\Http\Response;

class BlogController
{
    public function __construct(protected BlogService $service) {}

    public function index(Request $request): Response
    {
        return response()->html(view('blog', [
            'service' => $this->service->example(),
        ]));
    }
}