<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://arendasv74.vercel.app',
        'https://arendasv74-*.vercel.app',
        'https://*.trycloudflare.com',
    ],
    'allowed_origins_patterns' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];