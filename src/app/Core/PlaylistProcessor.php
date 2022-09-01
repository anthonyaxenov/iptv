<?php

declare(strict_types = 1);

namespace App\Core;

use Illuminate\Support\Collection;

final class PlaylistProcessor
{
    public Collection $playlists;

    protected string $updated_at;

    public function __construct()
    {
        $filepath = config_path('playlists.ini');
        $this->updated_at = date('d.m.Y h:i', filemtime($filepath));
        $this->playlists = collect(parse_ini_file($filepath, true))
            ->transform(function ($playlist, $id) {
                return empty($playlist['redirect'])
                    ? new Playlist((string)$id, $playlist)
                    : new RedirectedPlaylist((string)$id, $playlist['redirect']);
            });
    }

    public function hasId(string $id): bool
    {
        return in_array($id, $this->playlists->keys()->toArray());
    }

    public function playlist(string $id): Playlist|RedirectedPlaylist
    {
        !$this->hasId($id) && throw new \InvalidArgumentException("Плейлист с ID=$id не найден");
        return $this->playlists[$id];
    }

    public function check(string $id): bool
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->playlist($id)['pls']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_NOBODY, 1);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        return $code < 400;
    }

    protected function fetch(string $id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->playlist($id)->pls,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HEADER => false,
            CURLOPT_FAILONERROR => true,
        ]);
        $content = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $err_code = curl_errno($curl);
        $err_text = curl_error($curl);
        curl_close($curl);
        return [
            'content' => $content,
            'http_code' => $http_code,
            'err_code' => $err_code,
            'err_text' => $err_text,
        ];
    }

    protected function guessStatus(int $curl_err_code): string
    {
        return match ($curl_err_code) {
            0 => 'online',
            28 => 'timeout',
            5, 6, 7, 22, 35 => 'offline',
            default => 'error',
        };
    }

    public function parse(string $id): array
    {
        $fetched = $this->fetch($id);
        if ($fetched['err_code'] > 0) {
            return [
                'status' => $this->guessStatus($fetched['err_code']),
                'error' => [
                    'code' => $fetched['err_code'],
                    'message' => $fetched['err_text'],
                ],
            ];
        }
        $result['status'] = $this->guessStatus($fetched['err_code']);
        $result['encoding']['name'] = 'UTF-8';
        $result['encoding']['alert'] = false;
        if (($enc = mb_detect_encoding($fetched['content'], config('app.pls_encodings'))) !== 'UTF-8') {
            $fetched['content'] = mb_convert_encoding($fetched['content'], 'UTF-8', $enc);
            $result['encoding']['name'] = $enc;
            $result['encoding']['alert'] = true;
        }
        $matches = [];
        preg_match_all("/^#EXTINF:-?\d.*,\s*(.*)/m", $fetched['content'], $matches);
        $result['channels'] = array_map('trim', $matches[1]);
        $result['count'] = $fetched['http_code'] < 400 ? count($result['channels']) : 0;
        return $result;
    }

    /**
     * @return string
     */
    public function updatedAt(): string
    {
        return $this->updated_at;
    }
}
