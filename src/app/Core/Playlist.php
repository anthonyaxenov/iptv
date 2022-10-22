<?php

declare(strict_types = 1);

namespace App\Core;

class Playlist extends BasicPlaylist
{
    public ?string $name;

    public ?string $desc;

    public string $pls;

    public ?string $src;

    public string $url;

    /**
     * @throws \Exception
     */
    public function __construct(public string $id, array $params)
    {
        empty($params['pls']) && throw new \Exception(
            "Плейлист с ID=$id обязан иметь параметр pls или redirect"
        );
        $this->url = base_url($id);
        $this->name = empty($params['name']) ? "Плейлист #$id" : $params['name'];
        $this->desc = empty($params['desc']) ? null : $params['desc'];
        $this->pls = $params['pls'];
        $this->src = empty($params['src']) ? null : $params['src'];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,
            'desc' => $this->desc,
            'pls' => $this->pls,
            'src' => $this->src,
        ];
    }
}
