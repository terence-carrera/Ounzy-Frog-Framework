<?php

use Frog\Infrastructure\Routing\Router;
use Frog\Infrastructure\Routing\Route;

Route::get('/', [\Frog\App\Controllers\IndexController::class, 'index'])->name('index');
Route::get('/docs', fn() => response()->html(view('docs/getting-started', [
    'nav' => [
        ['label' => 'Quick Start', 'href' => '#quickstart'],
        ['label' => 'First App', 'href' => '#first-app'],
        ['label' => 'Routing', 'href' => '#routing'],
        ['label' => 'Controllers', 'href' => '#controllers'],
        ['label' => 'Views', 'href' => '#views'],
    ],
])))->name('docs');
Route::get('/docs/getting-started', fn() => response()->html(view('docs/getting-started', [
    'nav' => [
        ['label' => 'Quick Start', 'href' => '#quickstart'],
        ['label' => 'First App', 'href' => '#first-app'],
        ['label' => 'Routing', 'href' => '#routing'],
        ['label' => 'Controllers', 'href' => '#controllers'],
        ['label' => 'Views', 'href' => '#views'],
    ],
])))->name('docs.getting-started');
Route::get('/docs/core', fn() => response()->html(view('docs/core', [
    'nav' => [
        ['label' => 'Middleware', 'href' => '#middleware'],
        ['label' => 'Security', 'href' => '#security'],
        ['label' => 'Requests', 'href' => '#requests'],
        ['label' => 'Responses', 'href' => '#responses'],
        ['label' => 'Dependency Injection', 'href' => '#di'],
        ['label' => 'Services', 'href' => '#services'],
        ['label' => 'Database', 'href' => '#database'],
        ['label' => 'Caching', 'href' => '#cache'],
    ],
])))->name('docs.core');
Route::get('/docs/advanced', fn() => response()->html(view('docs/advanced', [
    'nav' => [
        ['label' => 'Changelog', 'href' => '#changelog'],
        ['label' => 'Mail', 'href' => '#mail'],
        ['label' => 'Keycloak', 'href' => '#keycloak'],
        ['label' => 'Use Cases', 'href' => '#use-cases'],
        ['label' => 'Troubleshooting', 'href' => '#troubleshooting'],
        ['label' => 'Best Practices', 'href' => '#best-practices'],
        ['label' => 'Recipes', 'href' => '#recipes'],
        ['label' => 'Extensibility', 'href' => '#extensibility'],
        ['label' => 'Configuration', 'href' => '#config'],
        ['label' => 'Environment Reference', 'href' => '#env-reference'],
        ['label' => 'Env Profiles', 'href' => '#env-profiles'],
        ['label' => 'Error Handling', 'href' => '#errors'],
        ['label' => 'Testing Guide', 'href' => '#testing-guide'],
        ['label' => 'CLI', 'href' => '#cli'],
        ['label' => 'CLI Reference', 'href' => '#cli-reference'],
        ['label' => 'CLI Cheatsheet', 'href' => '#cli-cheatsheet'],
        ['label' => 'Production Checklist', 'href' => '#production-checklist'],
        ['label' => 'Deployment', 'href' => '#deployment'],
    ],
])))->name('docs.advanced');

Route::get('/blog', [\Frog\App\Controllers\BlogController::class, 'index'])->name('blog');
