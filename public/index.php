<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Yason\WebsiteTemplate\Core\Env;

Env::load(dirname(__DIR__) . '/.env');

use Yason\WebsiteTemplate\Core\Router;

$router = new Router();

require_once __DIR__ . '/../routes/web.php';

$router->dispatch($_SERVER['REQUEST_URI']);