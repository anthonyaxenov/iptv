<?php

declare(strict_types=1);

namespace Controllers;

use Exception;
use Flight;

/**
 * Контроллер домашней страницы (списка плейлистов)
 */
class HomeController extends Controller
{
    /**
     * Отображает главную страницу с учётом пагинации списка плейлистов
     *
     * @param int $page Текущая страница списка
     * @return void
     * @throws Exception
     */
    public function index(int $page = 1): void
    {
        $redis = Flight::get('redis');

        // если пришёл любой get-параметр, то считаем его как id плейлиста и перебрасываем на страницу о нём
        if (Flight::request()->query->count() > 0) {
            $id = Flight::request()->query->keys()[0];
            Flight::redirect(base_url($id));
            die;
        }

        // иначе формируем и сортируем список при необходимости, рисуем страницу
        $perPage = 20;
        $playlists = $this->ini->playlists(false);
        $count = count($playlists);
        $pageCount = ceil($count / $perPage);
        $offset = max(0, ($page - 1) * $perPage);
        $list = array_slice($playlists, $offset, $perPage, true);

        view('list', [
            'updated_at' => $this->ini->updatedAt(),
            'count' => $count,
            'pages' => [
                'count' => $pageCount,
                'current' => $page,
            ],
            'playlists' => $list,
        ]);
    }

    /**
     * Отображает страницу FAQ
     *
     * @return void
     * @throws Exception
     */
    public function faq(): void
    {
        view('faq');
    }
}
