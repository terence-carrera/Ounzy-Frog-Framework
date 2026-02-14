<?php

return [
    'default' => env('DB_CONNECTION', 'sqlite'),
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', dirname(__DIR__) . '/storage/database.sqlite'),
            'options' => [],
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int)env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'frog'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [],
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int)env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'frog'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'options' => [],
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int)env('DB_PORT', 1433),
            'instance' => env('DB_INSTANCE', ''),
            'database' => env('DB_DATABASE', 'frog'),
            'username' => env('DB_USERNAME', 'sa'),
            'password' => env('DB_PASSWORD', ''),
            'encrypt' => filter_var(env('DB_ENCRYPT', false), FILTER_VALIDATE_BOOL),
            'trust_server_certificate' => filter_var(env('DB_TRUST_SERVER_CERT', false), FILTER_VALIDATE_BOOL),
            'options' => [],
        ],
    ],
];
