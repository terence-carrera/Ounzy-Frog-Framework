<?php

use Frog\Infrastructure\Env;
use Frog\Infrastructure\Config;
use Frog\Infrastructure\App;
use Frog\Infrastructure\Cache\CacheManager;
use Frog\Infrastructure\Database\DatabaseManager;
use Frog\Infrastructure\Mail\MailManager;

// Load environment variables
if (!file_exists(__DIR__ . '/../.env')) {
    error_log('[Frog] .env file not found, using defaults');
} else {
    Env::load(__DIR__ . '/../.env');
}

// Load configuration files
$config = new Config();
foreach (glob(__DIR__ . '/../config/*.php') as $file) {
    $name = basename($file, '.php');
    $config->set($name, require $file);
}

// Bind config to container
app()->container()->instance(Config::class, $config);
app()->container()->instance('config', $config);

// Core service bindings
app()->container()->singleton(DatabaseManager::class, fn() => new DatabaseManager(config('database', [])));
app()->container()->singleton(CacheManager::class, fn() => new CacheManager(config('cache', [])));
app()->container()->singleton(MailManager::class, fn() => new MailManager(config('mail', [])));
