<?php

use App\Controllers\AuthController;
use App\Controllers\AdminUserController;
use App\Controllers\AgentController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PaymentController;
use Framework\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('guest')->group(function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
});

Route::middleware('auth')->group(function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/agents', [AgentController::class, 'index']);
    $router->get('/agents/create', [AgentController::class, 'create']);
    $router->post('/agents', [AgentController::class, 'store']);
    $router->get('/agents/show', [AgentController::class, 'show']);
    $router->get('/agents/edit', [AgentController::class, 'edit']);
    $router->post('/agents/update', [AgentController::class, 'update']);
    $router->post('/agents/delete', [AgentController::class, 'destroy']);

    $router->get('/payments', [PaymentController::class, 'index']);
    $router->get('/payments/create', [PaymentController::class, 'create']);
    $router->post('/payments', [PaymentController::class, 'store']);
    $router->get('/payments/show', [PaymentController::class, 'show']);
    $router->get('/payments/edit', [PaymentController::class, 'edit']);
    $router->post('/payments/update', [PaymentController::class, 'update']);
    $router->post('/payments/delete', [PaymentController::class, 'destroy']);

    $router->get('/admin/users', [AdminUserController::class, 'index']);
    $router->get('/admin/users/create', [AdminUserController::class, 'create']);
    $router->post('/admin/users', [AdminUserController::class, 'store']);
    $router->get('/admin/users/edit', [AdminUserController::class, 'edit']);
    $router->post('/admin/users/update', [AdminUserController::class, 'update']);

    $router->post('/logout', [AuthController::class, 'logout']);
});