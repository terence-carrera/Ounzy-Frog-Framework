<?php

return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores' => [
        'array' => [
            'driver' => 'array',
        ],
        'file' => [
            'driver' => 'file',
            'path' => env('CACHE_PATH', dirname(__DIR__) . '/storage/cache'),
        ],
    ],
];
