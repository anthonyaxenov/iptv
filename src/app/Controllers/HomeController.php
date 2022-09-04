<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Core\PlaylistProcessor;
use App\Core\RedirectedPlaylist;
use Exception;
use Flight;

class HomeController extends Controller
{
    protected PlaylistProcessor $ini;

    public function __construct()
    {
        $this->ini = new PlaylistProcessor();
    }

    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        $size = config('app.page_size');
        return empty($size) || $size < 5 || $size > 100 ? 10 : $size;
    }

    /**
     * @throws Exception
     */
    public function index(int $page = 1)
    {
        if (Flight::request()->query->count() > 0) {
            $id = Flight::request()->query->keys()[0];
            Flight::redirect(base_url($id));
            die;
        }
        $per_page = $this->getPageSize();
        $list = $this->ini->playlists->where('redirect_id', null);
        if (config('app.sort_by')) {
            $list = $list->sortBy(config('app.sort_by'));
        }
        $list = $list->forPage($page, $per_page);
        view('list', [
            'updated_at' => $this->ini->updatedAt(),
            'count' => $this->ini->playlists->count(),
            'pages' => [
                'count' => (int)($this->ini->playlists->count() / $per_page),
                'current' => $page,
            ],
            'playlists' => $list->toArray(),
        ]);
    }

    /**
     * @throws Exception
     */
    public function faq()
    {
        view('faq');
    }

    /**
     * @throws Exception
     */
    public function details(string $id): void
    {
        $playlist = $this->ini->playlist($id);
        if ($playlist instanceof RedirectedPlaylist) {
            Flight::redirect(base_url($playlist->redirect_id . '/info'));
        }
        view('details', [
            'id' => $id,
            'playlist' => $playlist->toArray(),
            'info' => $this->ini->parse($id),
        ]);
    }

    /**
     * @throws Exception
     */
    public function ajax(string $id): void
    {
        $playlist = $this->ini->playlist($id);
        if ($playlist instanceof RedirectedPlaylist) {
            Flight::redirect(base_url($playlist->redirect_id . '/getInfo'));
        }
        Flight::json([
            'playlist' => $playlist->toArray(),
            'info' => $this->ini->parse($id),
        ]);
    }
}
