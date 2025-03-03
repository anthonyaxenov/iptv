<?php

declare(strict_types=1);

return [
    'cache' => bool(env('TWIG_USE_CACHE', true)) ? cache_path() . '/views' : false,
    'debug' => bool(env('TWIG_DEBUG', false)),
];
