<?php

declare(strict_types = 1);

namespace App\Core;

class RedirectedPlaylist extends BasicPlaylist
{
    /**
     * @throws \Exception
     */
    public function __construct(
        public string $id,
        public string $redirect_id,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'redirect_id' => $this->redirect_id,
        ];
    }
}
