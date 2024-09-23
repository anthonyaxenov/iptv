<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\IniFile;
use App\Core\Playlist;
use App\Exceptions\PlaylistNotFoundException;
use Exception;
use Flight;
use Random\RandomException;

/**
 * Абстрактный контроллер для расширения
 */
abstract class Controller
{
    /**
     * @var IniFile Класс для работы с ini-файлом плейлистов
     */
    protected IniFile $ini;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->ini = Flight::get('ini');
    }

    /**
     * Возвращает плейлист по его ID для обработки
     *
     * @param string $id
     * @param bool $asJson
     * @return Playlist
     * @throws Exception
     */
    protected function getPlaylist(string $id, bool $asJson = false): Playlist
    {
        if ($this->ini->getRedirection($id)) {
            Flight::redirect(base_url($this->ini->getRedirection($id) . ($asJson ? '/json' : '/details')));
            die;
        }

        try {
            return $this->ini->getPlaylist($id);
        } catch (PlaylistNotFoundException) {
            $this->notFound($id, $asJson);
            die;
        }
    }

    /**
     * Возвращает обработанный плейлист для ответа
     *
     * @param string $id ID плейлиста
     * @param bool $asJson Обрабатывать как json
     * @return array
     * @throws RandomException
     * @throws Exception
     */
    protected function getPlaylistResponse(string $id, bool $asJson = false): array
    {
        $playlist = $this->getPlaylist($id, $asJson);
        $playlist->download();
        $playlist->parse();
        return $playlist->toArray();
    }

    /**
     * Перебрасывает на страницу 404 при ненайденном плейлисте
     *
     * @param string $id ID плейлиста
     * @param bool $asJson Обрабатывать как json
     * @return void
     * @throws Exception
     */
    public function notFound(string $id, bool $asJson = false): void
    {
        Flight::response()->status(404)->sendHeaders();
        $asJson || view('notfound', ['id' => $id]);
    }
}
