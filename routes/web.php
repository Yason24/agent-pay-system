<?php

use Yason\WebsiteTemplate\Core\Support\Facades\Route;
use Yason\WebsiteTemplate\Controllers\HomeController;

Route::middleware('web')->group(function () {

    Route::get('/', [HomeController::class, 'index']);

});