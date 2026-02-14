<?php

return [
    'default' => env('MAIL_DRIVER', 'mail'),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Frog'),
    ],
    'drivers' => [
        'mail' => [
            'driver' => 'mail',
        ],
        'smtp' => [
            'driver' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => (int)env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'), // tls|ssl|none
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'timeout' => (int)env('MAIL_TIMEOUT', 10),
        ],
        'log' => [
            'driver' => 'log',
            'path' => env('MAIL_LOG_PATH', dirname(__DIR__) . '/storage/logs/mail.log'),
        ],
    ],
];
