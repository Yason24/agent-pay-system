<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;

class DashboardController extends Controller
{
    public function index(): string|Response
    {
        $user = app(AuthService::class)->user();

        if ($user === null) {
            return redirect('/login');
        }

        return $this->view('dashboard.index', [
            'title' => 'Кабинет',
            'user' => $user,
            'userFullName' => $user->fullName() !== '' ? $user->fullName() : 'Пользователь',
        ]);
    }
}