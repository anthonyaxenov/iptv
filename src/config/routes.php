<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\PlaylistController;

return [
    'GET /' => [HomeController::class, 'index'],
    'GET /page/@page:[0-9]+' => [HomeController::class, 'index'],
    'GET /faq' => [HomeController::class, 'faq'],
    'GET /@id:[a-zA-Z0-9_-]+' => [PlaylistController::class, 'download'],
    'GET /?[a-zA-Z0-9_-]+' => [PlaylistController::class, 'download'],
    'GET /@id:[a-zA-Z0-9_-]+/details' => [PlaylistController::class, 'details'],
    'GET /@id:[a-zA-Z0-9_-]+/json' => [PlaylistController::class, 'json'],
];
