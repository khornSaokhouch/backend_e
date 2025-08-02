<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Add sanctum if you're using Sanctum

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000','https://frontend-e-8c9k.vercel.app'], // ⚠️ Exact match!

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ✅ REQUIRED!
];
