<?php

use Yason\WebsiteTemplate\Controllers\HomeController;

/** @var $router \Yason\WebsiteTemplate\Core\Router */

$router->get('/', [
    HomeController::class,
    'index'
], [
    \Yason\WebsiteTemplate\Middleware\LoggerMiddleware::class
]);

//dd('ROUTES LOADED');