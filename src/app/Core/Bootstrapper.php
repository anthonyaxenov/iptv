<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AjaxController;
use App\Extensions\TwigFunctions;
use Flight;
use Illuminate\Support\Arr;
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
        $settings = Arr::dot(require_once config_path('app.php'));
        Arr::map($settings, function ($value, $key) {
            Flight::set("flight.$key", $value);
        });
        Flight::set('config', $settings);
    }

    /**
     * Загружает шаблонизатор и его расширения
     *
     * @return void
     */
    public static function bootTwig(): void
    {
        $filesystemLoader = new FilesystemLoader(config('views.path'));
        Flight::register(
            'view',
            Environment::class,
            [$filesystemLoader, config('twig')],
            function ($twig) {
                /** @var Environment $twig */
                Flight::set('twig', $twig);
                $twig->addExtension(new TwigFunctions());
                $twig->addExtension(new DebugExtension());
            }
        );
    }

    /**
     * Загружает маршруты
     *
     * @return void
     */
    public static function bootRoutes(): void
    {
        $routes = require_once config_path('routes.php');
        foreach ($routes as $route => $handler) {
            Flight::route($route, $handler);
        }
    }
}
