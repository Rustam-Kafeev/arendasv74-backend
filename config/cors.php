<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'register', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://arendasv74.vercel.app',  // Только конкретный домен
        'http://localhost:3000',           // Для локальной разработки
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // Важно для авторизации
];