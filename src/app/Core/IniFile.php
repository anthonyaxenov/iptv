<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\PlaylistNotFoundException;
use Exception;

/**
 * Класс для работы с ini-файлом плейлистов
 */
class IniFile
{
    /**
     * @var array Считанное из файла содержимое ini-файла
     */
    protected array $rawIni;

    /**
     * @var Playlist[] Коллекция подгруженных плейлистов
     */
    protected array $playlists = [];

    /**
     * @var string[] Карта переадресаций плейлистов
     */
    protected array $redirections = [];

    /**
     * @var string Дата последнего обновления списка
     */
    protected string $updated_at;

    /**
     * Считывает ini-файл и инициализирует объекты плейлистов
     *
     * @return void
     * @throws Exception
     */
    public function load(): void
    {
        $filepath = config_path('playlists.ini');
        $this->updated_at = date('d.m.Y h:i', filemtime($filepath));

        $this->rawIni = parse_ini_file($filepath, true);
        foreach ($this->rawIni as $id => $data) {
            $this->playlists[(string)$id] = $this->makePlaylist($id, $data);
        }
    }

    /**
     * Возвращает объекты плейлистов
     *
     * @param bool $all true - получить все, false - получить только НЕпереадресованные
     * @return Playlist[]
     */
    public function playlists(bool $all = true): array
    {
        if ($all) {
            return $this->playlists;
        }

        return array_filter($this->playlists, static fn ($playlist) => is_null($playlist->redirectId));
    }

    /**
     * Возвращает дату обновления ini-файла
     *
     * @return string
     */
    public function updatedAt(): string
    {
        return $this->updated_at;
    }

    /**
     * Возвращает ID плейлиста, на который нужно переадресовать указанный
     *
     * @param string $id ID плейлиста
     * @return string|null
     */
    public function getRedirection(string $id): ?string
    {
        return $this->redirections[$id] ?? null;
    }

    /**
     * Возвращает объект плейлиста
     *
     * @param string $id ID плейлиста
     * @return Playlist|null
     * @throws PlaylistNotFoundException
     */
    public function getPlaylist(string $id): ?Playlist
    {
        return $this->playlists[$id] ?? throw new PlaylistNotFoundException($id);
    }

    /**
     * Создаёт объекты плейлистов, рекурсивно определяя переадресации
     *
     * @param int|string $id ID плейлиста
     * @param array $params Описание плейлиста
     * @param string|null $redirectId ID для переадресации
     * @return Playlist
     * @throws Exception
     */
    protected function makePlaylist(int|string $id, array $params, ?string $redirectId = null): Playlist
    {
        $id = (string)$id;
        if (isset($params['redirect'])) {
            $this->redirections[$id] = $redirectId = (string)$params['redirect'];
            $params = $this->rawIni[$redirectId];
            return $this->makePlaylist($id, $params, $redirectId);
        }

        return new Playlist($id, $params, $redirectId);
    }
}
