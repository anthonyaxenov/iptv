<?php

declare(strict_types = 1);

return [
    'flight' => [
        // https://flightphp.com/learn#configuration
        'base_url' => env('APP_URL', 'http://localhost:8080'),
        'case_sensitive' => bool(env('FLIGHT_CASE_SENSITIVE', false)),
        'handle_errors' => bool(env('FLIGHT_HANDLE_ERRORS', true)),
        'log_errors' => bool(env('FLIGHT_LOG_ERRORS', true)),
        'views' => [
            'path' => views_path(),
            'extension' => '.twig',
        ],
    ],
    'twig' => [
        'cache' => bool(env('TWIG_CACHE', true)) ? cache_path() . '/views' : false,
        'debug' => bool(env('TWIG_DEBUG', false)),
    ],
    'app' => [
        'title' => env('APP_TITLE', 'IPTV Playlists'),
        'pls_encodings' => [
            'UTF-8',
            'CP1251',
            // 'CP866',
            // 'ISO-8859-5',
        ],
        'page_size' => (int)(env('PAGE_SIZE', 10)),
        'sort_by' => env('SORT_BY'),
    ],
];
