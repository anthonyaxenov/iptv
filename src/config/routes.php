<?php

use App\Controllers\ApiController;
use App\Controllers\BasicController;
use App\Controllers\WebController;

return [
    [
        'method' => 'GET',
        'path' => '/[page/{page:[0-9]+}]',
        'handler' => [WebController::class, 'home'],
        'name' => 'home',
    ],
    [
        'method' => 'GET',
        'path' => '/faq',
        'handler' => [WebController::class, 'faq'],
        'name' => 'faq',
    ],
    [
        'method' => 'GET',
        'path' => '/logo',
        'handler' => [WebController::class, 'logo'],
        'name' => 'logo',
    ],
    [
        'method' => 'GET',
        'path' => '/{code:[0-9a-zA-Z]+}',
        'handler' => [WebController::class, 'redirect'],
        'name' => 'redirect',
    ],
    [
        'method' => 'GET',
        'path' => '/{code:[0-9a-zA-Z]+}/details',
        'handler' => [WebController::class, 'details'],
        'name' => 'details',
    ],
    [
        'method' => 'GET',
        'path' => '/{code:[0-9a-zA-Z]+}/json',
        'handler' => [ApiController::class, 'json'],
        'name' => 'json',
    ],
    [
        'method' => '*',
        'path' => '/{path:.*}',
        'handler' => [BasicController::class, 'notFound'],
        'name' => 'not-found',
    ],
    // ...
];

