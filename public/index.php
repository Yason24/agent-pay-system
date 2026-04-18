<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

use Yason\WebsiteTemplate\Core\Container;
use Yason\WebsiteTemplate\Core\Router;
use Yason\WebsiteTemplate\Core\Request;
use Yason\WebsiteTemplate\Controllers\HomeController;

$container = new Container();

$router = new Router($container);

$router->get('/', [
    HomeController::class,
    'index'
]);

$router->dispatch(
    (new Request())->uri()
);