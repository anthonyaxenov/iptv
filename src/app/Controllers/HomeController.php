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
     * @throws Exception
     */
    public function index()
    {
        if (Flight::request()->query->count() > 0) {
            $id = Flight::request()->query->keys()[0];
            Flight::redirect(base_url("$id"));
            die;
        }
        view('list', [
            'updated_at' => $this->ini->updatedAt(),
            'count' => $this->ini->playlists->count(),
            'playlists' => $this->ini->playlists->where('redirect_id', null)->toArray(),
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
