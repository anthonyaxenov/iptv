<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\PlaylistNotFoundException;
use Exception;
use Flight;

/**
 * Контроллер методов получения описания плейлистов
 */
class PlaylistController extends Controller
{
    /**
     * Отправляет запрос с клиента по прямой ссылке плейлиста
     *
     * @param string $id ID плейлиста
     * @return void
     * @throws Exception
     */
    public function download(string $id): void
    {
        try {
            $playlist = $this->ini->getPlaylist($id);
            Flight::redirect($playlist->pls);
        } catch (PlaylistNotFoundException) {
            $this->notFound($id);
        }
        die;
    }

    /**
     * Отображает страницу описания плейлиста
     *
     * @param string $id ID плейлиста
     * @return void
     * @throws Exception
     */
    public function details(string $id): void
    {
        $result = $this->getPlaylistResponse($id);

        view('details', $result);
    }

    /**
     * Возвращает JSON с описанием плейлиста
     *
     * @param string $id ID плейлиста
     * @return void
     * @throws Exception
     */
    public function json(string $id): void
    {
        $result = $this->getPlaylistResponse($id, true);
        Flight::json($result);
    }
}
