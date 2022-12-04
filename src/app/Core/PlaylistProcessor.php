<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\PlaylistNotFoundException;
use Illuminate\Support\Collection;

/**
 * Обработчик списка плейлистов
 */
final class PlaylistProcessor
{
    /**
     * @var Collection Коллекция подгруженных плейлистов
     */
    public Collection $playlists;

    /**
     * @var string Дата последнего обновления списка
     */
    protected string $updated_at;

    /**
     * Конструктор
     */
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

    /**
     * Проверяет есть ли в списке плейлист по его id
     *
     * @param string $id
     * @return bool
     */
    public function hasId(string $id): bool
    {
        return $this->playlists->keys()->contains($id);
    }

    /**
     * Возвращает из коллекции указанный плейлист, если он существует
     *
     * @param string $id
     * @return Playlist|RedirectedPlaylist
     * @throws PlaylistNotFoundException
     */
    public function playlist(string $id): Playlist|RedirectedPlaylist
    {
        !$this->hasId($id) && throw new PlaylistNotFoundException($id);
        return $this->playlists[$id];
    }

    /**
     * Проверяет доступность плейлиста на третьей стороне
     *
     * @param string $id
     * @return bool
     * @throws PlaylistNotFoundException
     */
    public function check(string $id): bool
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->playlist($id)->pls,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => true,
        ]);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        return $code < 400;
    }

    /**
     * Получает содержимое плейлиста с третьей стороны
     *
     * @param string $id
     * @return array
     * @throws PlaylistNotFoundException
     */
    protected function fetch(string $id): array
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

    /**
     * Возвращает статус проверки плейлиста по коду ошибки curl
     *
     * @param int $curl_err_code
     * @return string
     */
    protected function guessStatus(int $curl_err_code): string
    {
        return match ($curl_err_code) {
            0 => 'online',
            28 => 'timeout',
            5, 6, 7, 22, 35 => 'offline',
            default => 'error',
        };
    }

    /**
     * Парсит полученный от третьей стороны плейлист
     *
     * @param string $id
     * @return array Информация о составе плейлиста
     * @throws PlaylistNotFoundException
     */
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
     * Возвращает дату последнего обновления списка плейлистов
     *
     * @return string
     */
    public function updatedAt(): string
    {
        return $this->updated_at;
    }
}
