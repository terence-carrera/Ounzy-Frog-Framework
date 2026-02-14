<?php

return [
    'enabled' => filter_var(env('KEYCLOAK_ENABLED', false), FILTER_VALIDATE_BOOL),
    'base_url' => env('KEYCLOAK_BASE_URL', 'http://localhost:8080'),
    'realm' => env('KEYCLOAK_REALM', 'master'),
    'client_id' => env('KEYCLOAK_CLIENT_ID', ''),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', ''),
    'jwks_cache_ttl' => (int)env('KEYCLOAK_JWKS_TTL', 3600),
    'issuer' => env('KEYCLOAK_ISSUER', ''),
];
