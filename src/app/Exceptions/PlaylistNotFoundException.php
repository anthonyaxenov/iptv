<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class PlaylistNotFoundException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("Плейлист $id не найден!");
    }
}
