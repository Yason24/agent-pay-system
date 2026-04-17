<?php

use Yason\WebsiteTemplate\Controllers\HomeController;

/** @var \Yason\WebsiteTemplate\Core\Router $router */

$router->get('/', [HomeController::class, 'index']);