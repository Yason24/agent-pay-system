<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT.'/vendor/autoload.php';

use Yason\WebsiteTemplate\Core\Container;
use Yason\WebsiteTemplate\Core\Router;
use Yason\WebsiteTemplate\Core\Request;
use Yason\WebsiteTemplate\Core\Kernel;

$container = new Container();

$router = new Router($container);

require ROOT.'/routes/web.php';

$request = new Request();

$kernel = new Kernel($container, $router);

$kernel->handle($request);