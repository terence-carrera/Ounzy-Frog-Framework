<?php

return [
    'name' => env('SESSION_NAME', 'FROGSESSID'),
    'lifetime' => (int)env('SESSION_LIFETIME', 120),
    'path' => env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN', ''),
    'secure' => filter_var(env('SESSION_SECURE', env('APP_ENV') === 'production'), FILTER_VALIDATE_BOOL),
    'httponly' => filter_var(env('SESSION_HTTPONLY', true), FILTER_VALIDATE_BOOL),
    'samesite' => env('SESSION_SAMESITE', 'Lax'),
];
