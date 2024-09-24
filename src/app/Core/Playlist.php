<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use Random\RandomException;

/**
 * Плейлист без редиректа
 */
class Playlist
{
    /**
     * @var string|null Название плейлиста
     */
    public ?string $name;

    /**
     * @var string|null Описание плейлиста
     */
    public ?string $desc;

    /**
     * @var string Прямой URL до файла плейлиста на третьей стороне
     */
    public string $pls;

    /**
     * @var string|null Источник плейлиста
     */
    public ?string $src;

    /**
     * @var string Ссылка на плейлист в рамках проекта
     */
    public string $url;

    /**
     * @var string|null Сырое содержимое плейлиста
     */
    protected ?string $rawContent = null;

    /**
     * @var array Обработанное содержимое плейлиста
     */
    protected array $parsedContent = [];

    /**
     * @var array Статус скачивания плейлиста
     */
    protected array $downloadStatus = [
        'httpCode' => 'unknown',
        'errCode' => 'unknown',
        'errText' => 'unknown',
        'possibleStatus' => 'unknown',
    ];

    /**
     * Конструктор
     *
     * @param string $id ID плейлиста
     * @param array $params Описание плейлиста
     * @param string|null $redirectId ID для переадресации
     * @throws Exception
     */
    public function __construct(
        public readonly string $id,
        array $params,
        public readonly ?string $redirectId = null
    ) {
        empty($params['pls']) && throw new Exception(
            "Плейлист с ID=$id обязан иметь параметр pls или redirect"
        );

        $this->url = base_url($id);
        $this->name = empty($params['name']) ? "Плейлист #$id" : $params['name'];
        $this->desc = empty($params['desc']) ? null : $params['desc'];
        $this->pls = $params['pls'];
        $this->src = empty($params['src']) ? null : $params['src'];
    }

    /**
     * Получает содержимое плейлиста с третьей стороны
     *
     * @return void
     */
    public function download(): void
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->pls,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => false,
            CURLOPT_FAILONERROR => true,
        ]);

        $content = curl_exec($curl);
        $this->rawContent = $content === false ? null : $content;
        $this->downloadStatus['httpCode'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->downloadStatus['errCode'] = curl_errno($curl);
        $this->downloadStatus['errText'] = curl_error($curl);
        $this->downloadStatus['possibleStatus'] = $this->guessStatus($this->downloadStatus['errCode']);
        curl_close($curl);
    }

    /**
     * Возвращает статус проверки плейлиста по коду ошибки curl
     *
     * @param int $curlErrCode
     * @return string
     */
    protected function guessStatus(int $curlErrCode): string
    {
        return match ($curlErrCode) {
            0 => 'online',
            28 => 'timeout',
            5, 6, 7, 22, 35 => 'offline',
            default => 'error',
        };
    }

    /**
     * Парсит полученный от третьей стороны плейлист
     *
     * @return array Информация о составе плейлиста
     * @throws RandomException
     */
    public function parse(): array
    {
        if (!empty($this->parsed())) {
            return $this->parsed();
        }

        $result = [
            'attributes' => [],
            'channels' => [],
            'groups' => [],
            'encoding' => [
                'name' => 'unknown',
                'alert' => false,
            ],
        ];

        if (is_null($this->rawContent)) {
            return $this->parsedContent = $result;
        }

        $enc = mb_detect_encoding($this->rawContent, config('app.pls_encodings'));
        $result['encoding']['name'] = $enc;
        if ($enc !== 'UTF-8') {
            $result['encoding']['alert'] = true;
            $this->rawContent = mb_convert_encoding($this->rawContent, 'UTF-8', $enc);
        }

        $lines = explode("\n", $this->rawContent);
        $isHeader = $isGroup = $isChannel = false;
        foreach ($lines as $line) {
            if (empty($line = trim($line))) {
                continue;
            }

            if (str_starts_with($line, '#EXTM3U ')) {
                $isHeader = true;
                $isGroup = $isChannel = false;

                $result['attributes'] = $this->parseAttributes($line);
                continue;
            }

            if (str_starts_with($line, '#EXTINF:')) {
                $isChannel = true;
                $isHeader = $isGroup = false;

                $combined = trim(substr($line, strpos($line, ',') + 1));
                $exploded = explode(',', $line);
                $attrs = $this->parseAttributes($exploded[0]);
                $tvgid = empty($attrs['tvg-id']) ? ' неизвестен' : "='{$attrs['tvg-id']}'";
                $name = trim($exploded[1] ?? "(канал без названия, tvg-id$tvgid)");
                $channel = [
                    '_id' => md5($name . random_int(1, 99999)),
                    'name' => trim($name),
                    'url' => null,
                    'group' => $attrs['group-title'] ?? null,
                    'attributes' => $attrs,
                ];

                unset($name, $attrs, $combined, $exploded);
                continue;
            }

            if (str_starts_with($line, '#EXTGRP:')) {
                $isGroup = true;
                $isHeader = false;

                if ($isChannel) {
                    $exploded = explode(':', $line);
                    $channel['group'] = $exploded[1];
                }
                continue;
            }

            if ($isChannel) {
                $channel['url'] = str_starts_with($line, 'http') ? $line : null;
                $logoUrl = $channel['attributes']['tvg-logo'] ?? null;
                if (is_string($logoUrl)) {
                    $logo = new ChannelLogo($logoUrl);
                    $logo->readFile();
                    $channel['logo'] = [
                        'base64' => $logo->asBase64(),
                        'size' => $logo->size(),
                        'mime-type' => $logo->mimeType(),
                    ];
                }
                $result['channels'][] = $channel;
                $isChannel = false;
                unset($channel);
            }
        }

        $groups = [];
        foreach ($result['channels'] as $channel) {
            $name = $channel['group'] ?? '(без группы)';
            $id = md5($name);
            if (empty($groups[$id])) {
                $groups[$id] = [
                    '_id' => $id,
                    'name' => $name,
                    'channels' => [],
                ];
            }
            $groups[$id]['channels'][] = $channel['_id'];
        }
        $result['groups'] = array_values($groups);

        return $this->parsedContent = $result;
    }

    /**
     * Парсит атрибуты строки и возвращает ассоциативный массив
     *
     * @param string $line
     * @return array
     */
    protected function parseAttributes(string $line): array
    {
        if (str_starts_with($line, '#')) {
            $line = trim(substr($line, strpos($line, ' ') + 1));
        }

        preg_match_all('#(?<key>[a-z-]+)="(?<value>.*)"#U', $line, $matches);
        return array_combine($matches['key'], $matches['value']);
    }

    /**
     * Возвращает содержимое объекта в виде массива
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,
            'desc' => $this->desc,
            'pls' => $this->pls,
            'src' => $this->src,
            'status' => $this->status(),
            'content' => [
                ...$this->parsed(),
                'channelCount' => count($this->parsed()['channels'])
            ],
        ];
    }

    /**
     * Возвращает ссылку на плейлист в рамках проекта
     *
     * @return string
     */
    public function url(): string
    {
        return sprintf('%s/%s', base_url(), $this->id);
    }

    /**
     * Возвращает статус скачивания плейлиста
     *
     * @return array|string[]
     */
    public function status(): array
    {
        return $this->downloadStatus;
    }

    /**
     * Возвращает обработанное содержимое плейлиста
     *
     * @return array
     */
    public function parsed(): array
    {
        return $this->parsedContent;
    }
}
