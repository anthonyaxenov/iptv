<?php

declare(strict_types=1);

use App\Core\Bootstrapper;
use Symfony\Component\Dotenv\Dotenv;

/*
|--------------------------------------------------------------------------
| Bootstrap all classes, settings, etc.
|--------------------------------------------------------------------------
*/

// autoload composer packages
require '../vendor/autoload.php';

// load .env parameters
(new Dotenv())->loadEnv(root_path() . '/.env');

// set up framework according to its config
Bootstrapper::bootSettings();

// set up Twig template engine
Bootstrapper::bootTwig();

// set up routes defined in config file
Bootstrapper::bootRoutes();

// start application
Flight::start();
