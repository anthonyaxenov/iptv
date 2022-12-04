<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class PlaylistNotFoundException extends Exception
{
    public function __construct(string $pls_code)
    {
        parent::__construct("Плейлист $pls_code не найден!");
    }
}
