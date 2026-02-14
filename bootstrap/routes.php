<?php

use Frog\Infrastructure\Routing\Router;
use Frog\Infrastructure\Routing\Route;

Route::get('/', [\Frog\App\Controllers\IndexController::class, 'index'])->name('index');
Route::get('/docs', fn() => response()->html(view('docs.base')))->name('docs');


