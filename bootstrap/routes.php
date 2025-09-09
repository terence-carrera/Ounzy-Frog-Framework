<?php

use Ounzy\FrogFramework\Routing\Router;
use Ounzy\FrogFramework\Routing\Route;

Route::get('/', [\Ounzy\FrogFramework\Controllers\IndexController::class, 'index'])->name('index');
