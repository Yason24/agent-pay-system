<?php

namespace App\Controllers;

use App\Services\AuditLogger;
use App\Services\AuthService;
use App\Services\LoginRateLimiter;
use App\Support\AuditAction;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

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

    public function login(Request $request, AuthService $auth, LoginRateLimiter $limiter, AuditLogger $audit): Response
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        if ($limiter->tooManyAttempts($ip)) {
            $seconds = $limiter->availableIn($ip);
            $_SESSION['auth_error'] = "Слишком много неудачных попыток входа. Повторите через {$seconds} сек.";

            $audit->log(AuditAction::LOGIN_FAILED, $request, $auth, [
                'status' => 'failed',
                'meta' => [
                    'reason' => 'rate_limited',
                    'available_in' => $seconds,
                ],
            ]);

            return Response::redirect('/login');
        }

        $login = trim((string) $request->input('login'));
        $password = (string) $request->input('password');

        if ($auth->attempt($login, $password)) {
            $limiter->clear($ip);
            unset($_SESSION['auth_error']);
            unset($_SESSION['auth_success']);

            $user = $auth->user();

            $audit->log(AuditAction::LOGIN_SUCCESS, $request, $auth, [
                'entity_type' => 'user',
                'entity_id' => $user !== null ? (int) $user->id : null,
            ]);

            return Response::redirect('/dashboard');
        }

        $limiter->hit($ip);
        $_SESSION['auth_error'] = 'Неверный логин или пароль.';

        $audit->log(AuditAction::LOGIN_FAILED, $request, $auth, [
            'status' => 'failed',
            'meta' => [
                'reason' => 'invalid_credentials',
            ],
        ]);

        return Response::redirect('/login');
    }

    public function logout(Request $request, AuthService $auth, AuditLogger $audit): Response
    {
        $user = $auth->user();
        $actorUserId = $user !== null ? (int) $user->id : null;
        $actorUserRole = $user !== null ? (string) $user->role : null;

        $auth->logout();

        $audit->log(AuditAction::LOGOUT, $request, $auth, [
            'actor_user_id' => $actorUserId,
            'actor_user_role' => $actorUserRole,
            'entity_type' => 'user',
            'entity_id' => $actorUserId,
        ]);

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
