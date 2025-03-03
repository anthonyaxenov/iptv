<?php

declare(strict_types=1);

return [
    'host' => env('REDIS_HOST', 'keydb'),
    'port' => (int)env('REDIS_PORT', 6379),
    'password' => env('REDIS_PASSWORD'),
    'db' => (int)env('REDIS_DB', 0),
    'ttl_days' => (int)env('REDIS_TTL_DAYS', 14) * 60 * 60 * 24, // 2 недели
];
