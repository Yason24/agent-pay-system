<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use Framework\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::middleware('guest')->group(function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->post('/logout', [AuthController::class, 'logout']);
});