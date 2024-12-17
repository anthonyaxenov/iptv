<?php

declare(strict_types=1);

namespace Core;

use Flight;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Сборщик приложения
 */
final class Bootstrapper
{
    /**
     * Загружает конфигурацию приложения в контейнер
     *
     * @return void
     */
    public static function bootSettings(): void
    {
        $config = require_once root_path('config.php');
        foreach ($config as $key => $value) {
            Flight::set($key, $value);
        }
        Flight::set('config', $config);
    }

    public static function bootCore(): void
    {
        $loader = new IniFile();
        $loader->load();

        Flight::set('ini', $loader);
    }

    /**
     * Загружает шаблонизатор и его расширения
     *
     * @return void
     */
    public static function bootTwig(): void
    {
        $twigCfg = [
            'cache' => config('twig.cache'),
            'debug' => config('twig.debug'),
        ];

        $closure = static function ($twig) {
            /** @var Environment $twig */
            Flight::set('twig', $twig);
            $twig->addExtension(new TwigFunctions());
            $twig->addExtension(new DebugExtension());
        };

        $loader = new FilesystemLoader(config('flight.views.path'));
        Flight::register('view', Environment::class, [$loader, $twigCfg], $closure);
    }

    /**
     * Загружает маршруты
     *
     * @return void
     */
    public static function bootRoutes(): void
    {
        $routes = require_once root_path('routes.php');
        foreach ($routes as $route => $handler) {
            Flight::route($route, $handler);
        }
    }

    public static function bootRedis(): void
    {
        $options = [
            'host' => config('redis.host'),
            'port' => config('redis.port'),
            // 'username' => config('redis.user'),
            // 'pass' => config('redis.password'),
            'connectTimeout' => 1,
        ];

        $redis = new \Redis($options);
        $redis->select((int)config('redis.database'));
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_JSON);

        Flight::set('redis', $redis);
    }
}
