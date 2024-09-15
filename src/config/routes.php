<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\PlaylistController;

return [
    'GET /' => (new HomeController())->index(...),
    'GET /page/@page:[0-9]+' => (new HomeController())->index(...),
    'GET /faq' => (new HomeController())->faq(...),
    'GET /@id:[a-zA-Z0-9_-]+' => (new PlaylistController())->download(...),
    'GET /?[a-zA-Z0-9_-]+' => (new PlaylistController())->download(...),
    'GET /@id:[a-zA-Z0-9_-]+/details' => (new PlaylistController())->details(...),
    'GET /@id:[a-zA-Z0-9_-]+/json' => (new PlaylistController())->json(...),
];
