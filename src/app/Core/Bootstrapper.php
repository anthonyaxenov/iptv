<?php

declare(strict_types = 1);

namespace App\Core;

use App\Controllers\AjaxController;
use App\Controllers\HomeController;
use App\Controllers\PlaylistController;
use App\Extensions\TwigFunctions;
use Flight;
use Illuminate\Support\Arr;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

final class Bootstrapper
{
    public static function bootEnv(): void
    {
        (new Dotenv())->loadEnv(root_path() . '/.env');
    }

    public static function bootSettings(): void
    {
        $settings = Arr::dot(require_once config_path('app.php'));
        Arr::map($settings, function ($value, $key) {
            Flight::set("flight.$key", $value);
        });
        Flight::set('config', $settings);
    }

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

    public static function bootRoutes(): void
    {
        Flight::route(
            'GET /',
            fn() => (new HomeController())->index()
        );
        Flight::route(
            'GET /page/@page:[0-9]+',
            fn($page) => (new HomeController())->index((int)$page)
        );
        Flight::route(
            'GET /faq',
            fn() => (new HomeController())->faq()
        );
        Flight::route(
            'GET /@id:[a-zA-Z0-9_-]+',
            fn($id) => (new PlaylistController())->download($id)
        );
        Flight::route(
            'GET /?[a-zA-Z0-9_-]+',
            fn($id) => (new PlaylistController())->download($id)
        );
        Flight::route(
            'GET /@id:[a-zA-Z0-9_-]+/details',
            fn($id) => (new PlaylistController())->details($id)
        );
        Flight::route(
            'GET /@id:[a-zA-Z0-9_-]+/json',
            fn($id) => (new PlaylistController())->json($id)
        );
    }
}
