<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Плейлист без редиректа
 */
class Playlist extends BasicPlaylist
{
    /**
     * @var string|null Название плейлиста
     */
    public ?string $name;

    /**
     * @var string|null Описание плейлиста
     */
    public ?string $desc;

    /**
     * @var string Прямой URL до файла плейлиста на третьей стороне
     */
    public string $pls;

    /**
     * @var string|null Источник плейлиста
     */
    public ?string $src;

    /**
     * @var string Ссылка на плейлист в рамках проекта
     */
    public string $url;

    /**
     * Конструктор
     *
     * @param string $id
     * @param array $params
     * @throws Exception
     */
    public function __construct(public string $id, array $params)
    {
        empty($params['pls']) && throw new Exception(
            "Плейлист с ID=$id обязан иметь параметр pls или redirect"
        );
        $this->url = base_url($id);
        $this->name = empty($params['name']) ? "Плейлист #$id" : $params['name'];
        $this->desc = empty($params['desc']) ? null : $params['desc'];
        $this->pls = $params['pls'];
        $this->src = empty($params['src']) ? null : $params['src'];
    }

    /**
     * @inheritDoc
     */
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
