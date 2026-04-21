<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Core\Controller;

class DashboardController extends Controller
{
    public function index(): string
    {
        $user = app(AuthService::class)->user();

        if ($user === null) {
            return \Framework\Core\Http\Response::redirect('/login');
        }

        return $this->view('dashboard.index', [
            'title' => 'Кабинет',
            'user' => $user,
        ]);
    }
}