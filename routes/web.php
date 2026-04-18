<?php

use Framework\Core\Support\Facades\Route;
use Yason\WebsiteTemplate\Controllers\HomeController;

Route::middleware('web')->group(function ($router) {

    $router->get('/', [
        HomeController::class,
        'index'
    ]);

});