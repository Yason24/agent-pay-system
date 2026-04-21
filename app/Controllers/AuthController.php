<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\LoginRateLimiter;
use Framework\Core\Controller;
use Framework\Core\Http\Response;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth.login', [
            'title' => 'Вход',
            'error' => $_SESSION['auth_error'] ?? null,
            'success' => $_SESSION['auth_success'] ?? null,
        ]);
    }

    public function showRegister(AuthService $auth): Response
    {
        if ($auth->hasRole('admin')) {
            return Response::redirect('/admin/users/create');
        }

        $_SESSION['auth_error'] = 'Публичная регистрация отключена. Обратитесь к администратору.';

        return Response::redirect('/login');
    }

    public function showForgotPassword(): string
    {
        return $this->view('auth.forgot-password', [
            'title' => 'Восстановление пароля',
        ]);
    }

    public function login(\Framework\Core\Request $request, AuthService $auth, LoginRateLimiter $limiter): Response
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        if ($limiter->tooManyAttempts($ip)) {
            $seconds = $limiter->availableIn($ip);
            $_SESSION['auth_error'] = "Слишком много неудачных попыток входа. Повторите через {$seconds} сек.";

            return Response::redirect('/login');
        }

        $login    = trim((string) $request->input('login'));
        $password = (string) $request->input('password');

        if ($auth->attempt($login, $password)) {
            $limiter->clear($ip);
            unset($_SESSION['auth_error']);
            unset($_SESSION['auth_success']);

            return Response::redirect('/dashboard');
        }

        $limiter->hit($ip);
        $_SESSION['auth_error'] = 'Неверный логин или пароль.';

        return Response::redirect('/login');
    }

    public function logout(AuthService $auth): Response
    {
        $auth->logout();

        return Response::redirect('/login');
    }

    public function register(AuthService $auth): Response
    {
        if ($auth->hasRole('admin')) {
            return Response::redirect('/admin/users/create');
        }

        $_SESSION['auth_error'] = 'Публичная регистрация отключена. Обратитесь к администратору.';

        return Response::redirect('/login');
    }
}
