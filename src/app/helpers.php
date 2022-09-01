<?php

declare(strict_types = 1);

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
 * Returns path to app
 *
 * @param string $path
 * @return string
 */
function app_path(string $path = ''): string
{
    return root_path("app/$path");
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
 * Return path to public part of application
 *
 * @param string $path
 * @return string
 */
function public_path(string $path = ''): string
{
    return root_path("public/$path");
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
    if (is_bool($value)) {
        return $value;
    }
    if (is_object($value)) {
        return true;
    }
    if (is_string($value)) {
        return match ($value = trim($value)) {
            '1', 'yes', 'true' => true,
            '0', 'no', 'false' => false,
            default => empty($value),
        };
    }
    if ($is_resource = is_resource($value)) {
        return $is_resource; // false if closed
    }
    return !empty($value);
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
    $config = Flight::get('config');
    if (isset($config["flight.$key"])) {
        return $config["flight.$key"];
    }
    if (isset($config[$key])) {
        return $config[$key];
    }
    $config = Arr::undot($config);
    if (Arr::has($config, $key)) {
        return Arr::get($config, $key);
    }
    return $default;
}
