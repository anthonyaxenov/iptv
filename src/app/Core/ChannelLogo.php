<?php

declare(strict_types=1);

namespace App\Core;

class ChannelLogo implements \Stringable
{
    /**
     * @var string Валидированная ссылка на изображение
     */
    public readonly string $url;

    /**
     * @var string|null Хэш от ссылки на изображение
     */
    public readonly ?string $hash;

    /**
     * @var string|null Путь к файлу изображению на диске
     */
    public readonly ?string $path;

    /**
     * @var string|null MIME-тип изображения
     */
    protected ?string $mimeType = null;

    /**
     * @var false|string|null Сырое изображение:
     * null -- не загружалось;
     * false -- ошибка загрузки;
     * string -- бинарные данные.
     */
    protected false|string|null $rawData = null;

    /**
     * Конструктор
     *
     * @param string $url Внешняя ссылка на изображение
     */
    public function __construct(string $url)
    {
        $url = $this->prepareUrl($url);
        if (is_string($url)) {
            $this->url = $url;
            $this->hash = md5($url);
            $this->path = cache_path("tv-logos/$this->hash");
        }
    }

    /**
     * Валидирует и очищает ссылку на изображение
     *
     * @param string $url
     * @return false|string
     */
    protected function prepareUrl(string $url): false|string
    {
        $url = filter_var(trim($url), FILTER_VALIDATE_URL);
        if ($url === false) {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        return $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
    }

    /**
     * Загружает сырое изображение по ссылке и определяет его MIME-тип
     *
     * @return bool
     */
    public function fetch(): bool
    {
        $this->rawData = @file_get_contents($this->url);
        $isFetched = is_string($this->rawData);
        if (!$isFetched) {
            return false;
        }

        $this->mimeType = $this->mimeType();
        return true;
    }

    /**
     * Сохраняет сырое изображение в кэш
     *
     * @return bool
     */
    public function store(): bool
    {
        return is_string($this->rawData)
            && $this->prepareCacheDir()
            && @file_put_contents($this->path, $this->rawData);
    }

    /**
     * Считывает изображение из кэша
     *
     * @return bool
     */
    public function readFile(): bool
    {
        if (!file_exists($this->path)) {
            return false;
        }

        $this->rawData = @file_get_contents($this->path);
        return is_string($this->rawData);
    }

    /**
     * Возвращает base64-кодированное изображение
     *
     * @return string|null
     */
    public function asBase64(): ?string
    {
        if (!is_string($this->rawData)) {
            return null;
        }

        return "data:$this->mimeType;base64," . base64_encode($this->rawData);
    }

    /**
     * Возвращает сырое изображение
     *
     * @return false|string|null
     */
    public function raw(): false|string|null
    {
        return $this->rawData;
    }

    /**
     * Проверяет готовность директории кэша изображений, создавая её при необходимости
     *
     * @return bool
     */
    public function prepareCacheDir(): bool
    {
        $cacheFileDir = cache_path('tv-logos');

        return is_dir($cacheFileDir)
            || @mkdir($cacheFileDir, 0775, true);
    }

    /**
     * Возвращает MIME-тип сырого изображения
     *
     * @return string|null
     */
    public function mimeType(): ?string
    {
        if (!is_string($this->rawData)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($this->rawData) ?: null;
    }

    /**
     * Возвращает размер сырого изображения в байтах
     *
     * @return int
     */
    public function size(): int
    {
        return strlen((string)$this->rawData);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->asBase64();
    }
}
