<?php

declare(strict_types=1);

return [
    // https://flightphp.com/learn#configuration
    'flight.base_url' => env('APP_URL', 'http://localhost:' . env('IPTV_NGNIX_PORT')),
    'flight.case_sensitive' => bool(env('FLIGHT_CASE_SENSITIVE', false)),
    'flight.handle_errors' => bool(env('FLIGHT_HANDLE_ERRORS', true)),
    'flight.log_errors' => bool(env('FLIGHT_LOG_ERRORS', true)),
    'flight.views.path' => views_path(),
    'flight.views.extension' => '.twig',

    'twig.cache' => bool(env('TWIG_CACHE', true)) ? cache_path() . '/views' : false,
    'twig.debug' => bool(env('TWIG_DEBUG', false)),

    'app.title' => env('APP_TITLE', 'IPTV Playlists'),
    'app.pls_encodings' => [
        'UTF-8',
        'CP1251',
        // 'CP866',
        // 'ISO-8859-5',
    ],

    'redis.host' => env('REDIS_HOST', 'iptv-keydb'),
    'redis.port' => (int)env('REDIS_PORT', 6379),
    // 'redis.user' => env('REDIS_USER'),
    // 'redis.password' => env('REDIS_PASS'),
    'redis.database' => (int)env('REDIS_DB', 0),
];
