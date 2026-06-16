<?php

return [
    'token' => env('METRICS_TOKEN'),
    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => (int) env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD'),
        'database' => (int) env('METRICS_REDIS_DB', 1),
    ],
];
