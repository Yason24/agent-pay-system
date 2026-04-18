<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

use Yason\WebsiteTemplate\Core\Env;
use Yason\WebsiteTemplate\Core\Router;

Env::load(ROOT . '/.env');

$router = new Router();

require ROOT . '/routes/web.php';

$router->dispatch($_SERVER['REQUEST_URI']);