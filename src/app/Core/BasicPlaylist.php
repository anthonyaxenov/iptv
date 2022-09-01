<?php

declare(strict_types = 1);

namespace App\Core;

use Illuminate\Contracts\Support\Arrayable;

abstract class BasicPlaylist implements Arrayable
{
    public string $id;

    public function url(): string
    {
        return sprintf('%s/%s', base_url(), $this->id);
    }
}
