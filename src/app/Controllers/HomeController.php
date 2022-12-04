<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\PlaylistProcessor;
use Exception;
use Flight;

/**
 * Контроллер домашней страницы (списка плейлистов)
 */
class HomeController extends Controller
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
     * Возвращает размер одной страницы списка плейлистов
     *
     * @return int Указанный в конфиге размер либо 10, если он выходит за диапазоны от 5 до 100
     */
    protected function getPageSize(): int
    {
        $size = config('app.page_size');
        return empty($size) || $size < 5 || $size > 100 ? 10 : $size;
    }

    /**
     * Отображает главную страницу на указанной странице списка плейлистов
     *
     * @param int $page
     * @return void
     * @throws Exception
     */
    public function index(int $page = 1): void
    {
        // если пришёл любой get-параметр, то считаем его как id плейлиста и перебрасываем на страницу о нём
        if (Flight::request()->query->count() > 0) {
            $id = Flight::request()->query->keys()[0];
            Flight::redirect(base_url($id));
            die;
        }
        // иначе формируем и сортируем список при необходимости, рисуем страницу
        $per_page = $this->getPageSize();
        $list = $this->ini->playlists->where('redirect_id', null);
        if ($sort_by = config('app.sort_by')) {
            $list = $list->sortBy($sort_by);
        }
        $list = $list->forPage($page, $per_page);
        view('list', [
            'updated_at' => $this->ini->updatedAt(),
            'count' => $this->ini->playlists->count(),
            'pages' => [
                'count' => ceil($this->ini->playlists->count() / $per_page),
                'current' => $page,
            ],
            'playlists' => $list->toArray(),
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
