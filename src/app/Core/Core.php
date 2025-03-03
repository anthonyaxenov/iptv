<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\TwigExtention as IptvTwigExtension;
use Dotenv\Dotenv;
use InvalidArgumentException;
use Redis;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Error\LoaderError;

/**
 * Загрузчик приложения
 */
final class Core
{
    /**
     * @var Core
     */
    private static Core $instance;

    /**
     * @var App
     */
    protected App $app;

    /**
     * @var array Конфигурация приложения
     */
    protected array $config = [];

    /**
     * @var Redis
     */
    protected Redis $redis;

    /**
     * @var IniFile
     */
    protected IniFile $iniFile;

    /**
     * Закрытый конструктор
     */
    private function __construct()
    {
    }

    /**
     * Возвращает объект приложения
     *
     * @return Core
     */
    public static function get(): Core
    {
        return self::$instance ??= new self();
    }

    /**
     * Загружает приложение
     *
     * @return App
     * @throws LoaderError
     */
    public function boot(): App
    {
        $this->app = AppFactory::create();

        $this->bootSettings();
        $this->bootRoutes();
        $this->bootTwig();
        $this->bootRedis();
        $this->bootIni();

        return $this->app;
    }

    /**
     * Возвращает значение из конфига
     *
     * @param string $key Ключ в формате "config.key"
     * @param mixed|null $default Значение по умолчанию
     * @return mixed
     */
    public function config(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        return $this->config[$parts[0]][$parts[1]] ?? $default;
    }

    /**
     * @return Redis
     */
    public function redis(): Redis
    {
        return $this->redis;
    }

    /**
     * @return IniFile
     */
    public function ini(): IniFile
    {
        return $this->iniFile;
    }

    /**
     * @return App
     */
    public function app(): App
    {
        return $this->app;
    }

    /**
     * Загружает файл .env или .env.$env
     *
     * @param string $env
     * @return array
     */
    protected function loadDotEnvFile(string $env = ''): array
    {
        $filename = empty($env) ? '.env' : ".env.$env";
        if (!file_exists(root_path($filename))) {
            return [];
        }

        $dotenv = Dotenv::createMutable(root_path(), $filename);
        return $dotenv->safeLoad();
    }

    /**
     * Загружает конфигурационные файлы
     *
     * @return void
     */
    protected function bootSettings(): void
    {
        $env = $this->loadDotEnvFile();

        if (!empty($env['APP_ENV'])) {
            $this->loadDotEnvFile($env['APP_ENV']);
        }

        foreach (glob(config_path() . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->config += [$key => require_once $file];
        }
    }

    /**
     * Загружает маршруты
     *
     * @return void
     * @see https://www.slimframework.com/docs/v4/objects/routing.html
     */
    protected function bootRoutes(): void
    {
        foreach ($this->config['routes'] as $route) {
            if (is_array($route['method'])) {
                $definition = $this->app->map($route['method'], $route['path'], $route['handler']);
            } else {
                $isPossible = in_array($route['method'], ['GET', 'POST', 'OPTIONS', 'PUT', 'PATCH', 'DELETE']);

                $func = match (true) {
                    $route['method'] === '*' => 'any',
                    $isPossible => strtolower($route['method']),
                    default => throw new InvalidArgumentException(sprintf('Неверный HTTP метод %s', $route['method']))
                };

                $definition = $this->app->$func($route['path'], $route['handler']);
            }

            if (!empty($route['name'])) {
                $definition->setName($route['name']);
            }
        }
    }

    /**
     * Загружает шаблонизатор и его расширения
     *
     * @return void
     * @throws LoaderError
     * @see https://www.slimframework.com/docs/v4/features/twig-view.html
     */
    protected function bootTwig(): void
    {
        $twig = Twig::create(root_path('views'), $this->config['twig']);
        $twig->addExtension(new IptvTwigExtension());
        $this->app->add(TwigMiddleware::create($this->app, $twig));
    }

    /**
     * Инициализирует подключение к Redis
     *
     * @return void
     * @see https://github.com/phpredis/phpredis/?tab=readme-ov-file
     */
    protected function bootRedis(): void
    {
        $options = [
            'host' => $this->config['redis']['host'],
            'port' => (int)$this->config['redis']['port'],
        ];

        if (!empty($this->config['redis']['password'])) {
            $options['auth'] = $this->config['redis']['password'];
        }

        $this->redis = new Redis($options);
        $this->redis->select((int)$this->config['redis']['db']);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
    }

    /**
     * Инициализирует объект ini-файла
     *
     * @return void
     */
    protected function bootIni(): void
    {
        $this->iniFile = new IniFile();
    }
}
