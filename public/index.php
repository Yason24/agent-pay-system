<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

use Yason\WebsiteTemplate\Core\Application;
use Yason\WebsiteTemplate\Core\Request;
use Yason\WebsiteTemplate\Core\Router;

$app = new Application(ROOT);   // 🔥 ВАЖНО

$router = new Router($app);

$router->get('/', [
    Yason\WebsiteTemplate\Controllers\HomeController::class,
    'index'
]);

$router->dispatch(
    (new Request())->uri()
);