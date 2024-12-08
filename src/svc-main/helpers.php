<?php

declare(strict_types=1);

use flight\Engine;
use flight\net\Response;
use Illuminate\Support\Arr;

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
    return rtrim(sprintf('%s/%s', config('flight.base_url'), $route), '/');
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
    return $_ENV[$key] ?? $default;
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
 * Returns response object
 *
 * @return Response
 */
function response(): Response
{
    return Flight::response();
}

/**
 * Returns app object
 *
 * @return Engine
 */
function app(): Engine
{
    return Flight::app();
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
    return Flight::get('config')[$key] ?? $default;
}
