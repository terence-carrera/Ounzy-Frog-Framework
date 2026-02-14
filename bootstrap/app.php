<?php

use Frog\Infrastructure\Env;
use Frog\Infrastructure\Config;
use Frog\Infrastructure\App;

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


