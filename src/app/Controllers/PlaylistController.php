<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\{
    PlaylistProcessor,
    RedirectedPlaylist};
use App\Exceptions\PlaylistNotFoundException;
use Exception;
use Flight;

/**
 * Контроллер методов получения описания плейлистов
 */
class PlaylistController extends Controller
{
    /**
     * @var PlaylistProcessor Обработчик ini-списка
     */
    protected PlaylistProcessor $ini;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->ini = new PlaylistProcessor();
    }

    /**
     * Отправляет запрос с клиента по прямой ссылке плейлиста
     *
     * @param $id
     * @return void
     * @throws Exception
     */
    public function download($id): void
    {
        try {
            $playlist = $this->ini->playlist($id);
            if ($playlist instanceof RedirectedPlaylist) {
                Flight::redirect(base_url($playlist->redirect_id));
                die;
            }
            Flight::redirect($playlist->pls);
        } catch (PlaylistNotFoundException) {
            $this->notFound($id);
        }
        die;
    }

    /**
     * Отображает страницу описания плейлиста
     *
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function details(string $id): void
    {
        try {
            $playlist = $this->ini->playlist($id);
            if ($playlist instanceof RedirectedPlaylist) {
                Flight::redirect(base_url($playlist->redirect_id . '/details'));
                die;
            }
            view('details', [
                ...$playlist->toArray(),
                ...$this->ini->parse($id),
            ]);
        } catch (PlaylistNotFoundException) {
            $this->notFound($id);
        }
    }

    /**
     * Возвращает JSON с описанием плейлиста
     *
     * @param string $id
     * @return void
     * @throws Exception
     */
    public function json(string $id): void
    {
        try {
            $playlist = $this->ini->playlist($id);
            if ($playlist instanceof RedirectedPlaylist) {
                Flight::redirect(base_url($playlist->redirect_id . '/json'));
                die;
            }
            Flight::json([
                ...$playlist->toArray(),
                ...$this->ini->parse($id),
            ]);
        } catch (PlaylistNotFoundException) {
            Flight::response()->status(404)->sendHeaders();
            Flight::json(['error' => ['message' => 'Playlist not found']]);
        }
    }
}
