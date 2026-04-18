<?php

use Framework\Core\Support\Facades\Route;
use App\Controllers\HomeController;

/*Route::middleware('web')->group(function ($router) {

    $router->get('/', 'App\Controllers\HomeController@index');

});*/

Route::get('/', function () {
    return 'HOME PAGE WORKS 🔥';
});