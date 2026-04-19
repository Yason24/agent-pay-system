<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Core\Controller;

class DashboardController extends Controller
{
    public function index(): string
    {
        $user = app(AuthService::class)->user();

        return $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'user' => $user,
        ]);
    }
}