<?php

use App\Controllers\AdminAgentController;
use App\Controllers\AuthController;
use App\Controllers\AdminUserController;
use App\Controllers\AgentController;
use App\Controllers\DashboardController;
use App\Controllers\HistoryController;
use App\Controllers\HomeController;
use App\Controllers\PaymentController;
use App\Controllers\RequestController;
use Framework\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::middleware('guest')->group(function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
});

Route::middleware('auth')->group(function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);

    $router->middleware('role:admin')->group(function ($router) {
        $router->get('/admin/users', [AdminUserController::class, 'index']);
        $router->get('/admin/users/create', [AdminUserController::class, 'create']);
        $router->post('/admin/users', [AdminUserController::class, 'store']);
        $router->get('/admin/users/edit', [AdminUserController::class, 'edit']);
        $router->post('/admin/users/update', [AdminUserController::class, 'update']);
        $router->post('/admin/users/reset-password', [AdminUserController::class, 'resetPassword']);
    });

    $router->middleware('role:dispatcher,accountant,admin')->group(function ($router) {
        $router->get('/agents', [AdminAgentController::class, 'index']);
        $router->get('/agents/show', [AdminAgentController::class, 'show']);

        $router->get('/history', [HistoryController::class, 'index']);
        $router->get('/requests', [RequestController::class, 'index']);
        $router->post('/requests/take', [RequestController::class, 'take']);
        $router->post('/requests/complete', [RequestController::class, 'complete']);

        $router->get('/payments', [PaymentController::class, 'index']);
        $router->get('/payments/create', [PaymentController::class, 'create']);
        $router->post('/payments', [PaymentController::class, 'store']);
        $router->get('/payments/show', [PaymentController::class, 'show']);
        $router->get('/payments/edit', [PaymentController::class, 'edit']);
        $router->post('/payments/update', [PaymentController::class, 'update']);
        $router->post('/payments/delete', [PaymentController::class, 'destroy']);
    });

    $router->middleware('role:agent')->group(function ($router) {
        $router->get('/cabinet', [AgentController::class, 'index']);
        $router->get('/my/balance', [HistoryController::class, 'myIndex']);
        $router->get('/my/history', [HistoryController::class, 'myIndex']);
        $router->get('/my/requests', [RequestController::class, 'myIndex']);
        $router->get('/my/payments', [PaymentController::class, 'myIndex']);
        $router->get('/requests/create', [RequestController::class, 'create']);
        $router->post('/requests/store', [RequestController::class, 'store']);
    });

    $router->post('/logout', [AuthController::class, 'logout']);
});