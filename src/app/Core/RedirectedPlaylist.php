<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Плейлист с редиректом
 */
class RedirectedPlaylist extends BasicPlaylist
{
    /**
     * Конструктор
     *
     * @param string $id
     * @param string $redirect_id
     */
    public function __construct(
        public string $id,
        public string $redirect_id,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'redirect_id' => $this->redirect_id,
        ];
    }
}
