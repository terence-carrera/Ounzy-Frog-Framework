<?php

return [
    'csrf' => [
        'enabled' => filter_var(env('CSRF_ENABLED', true), FILTER_VALIDATE_BOOL),
        'token_key' => env('CSRF_TOKEN_KEY', '_csrf_token'),
        'header' => env('CSRF_HEADER', 'X-CSRF-TOKEN'),
        'field' => env('CSRF_FIELD', '_token'),
        'methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
    ],
];
