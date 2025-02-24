<?php

declare(strict_types=1);

namespace Controllers;

use Core\ChannelLogo;
use Exception;
use Exceptions\PlaylistNotFoundException;
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

    /**
     * Возвращает логотип канала, кэшируя при необходимости
     *
     * @return void
     */
    public function logo(): void
    {
        $input = Flight::request()->query['url'] ?? null;

        $logo = new ChannelLogo($input);
        if (!$logo->readFile()) {
            $logo->fetch();
        }

        if ($logo->size() === 0) {
            $logo->setDefault();
        }

        $logo->store();
        $body = $logo->raw();
        $size = $logo->size();
        $mime = $logo->mimeType();

        Flight::response()
            ->write($body)
            ->header('Content-Type', $mime)
            ->header('Content-Length', (string)$size);
    }
}
