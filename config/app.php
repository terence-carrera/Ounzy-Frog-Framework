<?php

return [
    'name' => env('APP_NAME', 'Frog'),
    'version' => env('APP_VERSION', '0.1.0'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', 'http://localhost'),
];
