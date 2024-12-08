<?php

declare(strict_types=1);

use Core\Bootstrapper;
use Symfony\Component\Dotenv\Dotenv;

/*
|--------------------------------------------------------------------------
| Bootstrap all classes, settings, etc.
|--------------------------------------------------------------------------
*/

require '../vendor/autoload.php';
(new Dotenv())->loadEnv(root_path() . '/.env');
Bootstrapper::bootSettings();
Bootstrapper::bootTwig();
Bootstrapper::bootCore();
Bootstrapper::bootRoutes();
Flight::start();
