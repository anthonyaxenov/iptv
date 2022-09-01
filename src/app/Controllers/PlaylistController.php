<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Core\PlaylistProcessor;
use App\Core\RedirectedPlaylist;
use Exception;
use Flight;

class PlaylistController extends Controller
{
    protected PlaylistProcessor $ini;

    public function __construct()
    {
        $this->ini = new PlaylistProcessor();
    }

    /**
     * @throws Exception
     */
    public function download($id)
    {
        $playlist = $this->ini->playlist($id);
        if ($playlist instanceof RedirectedPlaylist) {
            Flight::redirect(base_url($playlist->redirect_id));
            die;
        }
        Flight::redirect($playlist->pls);
    }

    /**
     * @throws Exception
     */
    public function details(string $id): void
    {
        $playlist = $this->ini->playlist($id);
        if ($playlist instanceof RedirectedPlaylist) {
            Flight::redirect(base_url($playlist->redirect_id . '/details'));
            die;
        }
        view('details', [
            ...$playlist->toArray(),
            ...$this->ini->parse($id),
        ]);
    }

    /**
     * @throws Exception
     */
    public function json(string $id): void
    {
        $playlist = $this->ini->playlist($id);
        if ($playlist instanceof RedirectedPlaylist) {
            Flight::redirect(base_url($playlist->redirect_id . '/json'));
            die;
        }
        Flight::json([
            ...$playlist->toArray(),
            ...$this->ini->parse($id),
        ]);
    }
}
