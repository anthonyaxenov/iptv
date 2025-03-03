<?php

declare(strict_types=1);

use App\Core\Core;
use App\Core\IniFile;
use Slim\App;

/**
 * Returns path to root application directory
 *
 * @param string $path
 * @return string
 */
function root_path(string $path = ''): string
{
    return rtrim(sprintf('%s/%s', dirname($_SERVER['DOCUMENT_ROOT']), $path), '/');
}

/**
 * Return path to application configuration directory
 *
 * @param string $path
 * @return string
 */
function config_path(string $path = ''): string
{
    return root_path("config/$path");
}

/**
 * Returns path to app cache
 *
 * @param string $path
 * @return string
 */
function cache_path(string $path = ''): string
{
    return root_path("cache/$path");
}

/**
 * Returns path to app views
 *
 * @param string $path
 * @return string
 */
function views_path(string $path = ''): string
{
    return root_path("views/$path");
}

/**
 * Returns base URL
 *
 * @param string $route
 * @return string
 */
function base_url(string $route = ''): string
{
    return rtrim(sprintf('%s/%s', env('APP_URL'), $route), '/');
}

/**
 * Returns value of environment var
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

/**
 * Renders template
 *
 * @param mixed $template
 * @param array $data
 * @return void
 * @throws Exception
 */
function view(mixed $template, array $data = []): void
{
    $template = str_contains($template, '.twig') ? $template : "$template.twig";
    /** @noinspection PhpVoidFunctionResultUsedInspection */
    echo Flight::view()->render($template, $data);
}

/**
 * Returns core object
 *
 * @return Core
 */
function core(): Core
{
    return Core::get();
}

/**
 * Returns app object
 *
 * @return App
 */
function app(): App
{
    return Core::get()->app();
}

/**
 * Returns any value as boolean
 *
 * @param mixed $value
 * @return bool
 */
function bool(mixed $value): bool
{
    is_string($value) && $value = strtolower(trim($value));
    if (in_array($value, [true, 1, '1', '+', 'y', 'yes', 'on', 'true', 'enable', 'enabled'], true)) {
        return true;
    }
    if (in_array($value, [false, 0, '0', '-', 'n', 'no', 'off', 'false', 'disable', 'disabled'], true)) {
        return false;
    }
    return (bool)$value;
}

/**
 * Get config values
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function config(string $key, mixed $default = null): mixed
{
    return Core::get()->config($key, $default);
}

/**
 * Get Redis instance
 *
 * @return Redis
 */
function redis(): Redis
{
    return Core::get()->redis();
}

/**
 * Get ini-file instance
 *
 * @return IniFile
 */
function ini(): IniFile
{
    return Core::get()->ini();
}
