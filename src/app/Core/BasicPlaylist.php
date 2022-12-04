<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Базовый класс плейлиста
 */
abstract class BasicPlaylist implements Arrayable
{
    /**
     * @var string ID плейлиста
     */
    public string $id;

    /**
     * Возвращает ссылку на плейлист в рамках проекта
     *
     * @return string
     */
    public function url(): string
    {
        return sprintf('%s/%s', base_url(), $this->id);
    }
}
