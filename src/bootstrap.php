<?php

declare(strict_types = 1);

use App\Core\Bootstrapper;

// autoload composer packages
require 'vendor/autoload.php';

// load .env parameters
Bootstrapper::bootEnv();

// set up framework according to its config
Bootstrapper::bootSettings();

// set up Twig template engine
Bootstrapper::bootTwig();

// set up routes defined in config file
Bootstrapper::bootRoutes();
