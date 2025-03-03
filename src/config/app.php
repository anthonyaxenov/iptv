<?php

declare(strict_types=1);

return [
    'base_url' => env('APP_URL', 'http://localhost:8080'),
    'debug' => bool(env('APP_DEBUG', false)),
    'env' => env('APP_ENV', env('IPTV_ENV', 'prod')),
    'title' => env('APP_TITLE', 'IPTV Плейлисты'),
    'user_agent' => env('USER_AGENT'),
    'page_size' => (int)env('PAGE_SIZE', 10),
    'pls_encodings' => [
        'UTF-8',
        'CP1251',
        // 'CP866',
        // 'ISO-8859-5',
    ],
];
