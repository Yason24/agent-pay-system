<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\HashService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class AdminUserController extends Controller
{
    public function index(AuthService $auth): string|Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $success = $_SESSION['users_success'] ?? null;
        $error = $_SESSION['users_error'] ?? null;

        unset($_SESSION['users_success'], $_SESSION['users_error']);

        $users = User::query()->orderBy('id', 'DESC')->get();

        return $this->view('admin.users.index', [
            'title' => 'Пользователи',
            'users' => $users,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function create(AuthService $auth): string|Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $errors = $_SESSION['users_create_errors'] ?? [];
        $old = $_SESSION['users_create_old'] ?? [];

        unset($_SESSION['users_create_errors'], $_SESSION['users_create_old']);

        return $this->view('admin.users.create', [
            'title' => 'Создать пользователя',
            'errors' => $errors,
            'old' => $old,
            'roles' => User::roles(),
        ]);
    }

    public function store(Request $request, AuthService $auth, HashService $hash): Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $payload = [
            'name' => trim((string) $request->input('name', '')),
            'email' => trim((string) $request->input('email', '')),
            'password' => (string) $request->input('password', ''),
            'role' => trim((string) $request->input('role', 'agent')),
        ];

        $errors = $this->validateCreatePayload($payload);

        if (User::findByEmail($payload['email'])) {
            $errors['email'] = 'Пользователь с таким email уже существует.';
        }

        if ($errors !== []) {
            unset($payload['password']);
            $_SESSION['users_create_errors'] = $errors;
            $_SESSION['users_create_old'] = $payload;

            return Response::redirect('/admin/users/create');
        }

        User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $hash->make($payload['password']),
            'role' => $payload['role'],
        ]);

        unset($_SESSION['users_create_errors'], $_SESSION['users_create_old']);
        $_SESSION['users_success'] = 'Пользователь успешно создан.';

        return Response::redirect('/admin/users');
    }

    public function edit(Request $request, AuthService $auth): string|Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $user = User::find((int) $request->input('id', 0));

        if ($user === null) {
            $_SESSION['users_error'] = 'Пользователь не найден.';

            return Response::redirect('/admin/users');
        }

        $errors = $_SESSION['users_edit_errors'] ?? [];
        $old = $_SESSION['users_edit_old'] ?? [];

        unset($_SESSION['users_edit_errors'], $_SESSION['users_edit_old']);

        return $this->view('admin.users.edit', [
            'title' => 'Изменить роль пользователя',
            'userRecord' => $user,
            'errors' => $errors,
            'old' => $old,
            'roles' => User::roles(),
        ]);
    }

    public function update(Request $request, AuthService $auth): Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $user = User::find((int) $request->input('id', 0));

        if ($user === null) {
            $_SESSION['users_error'] = 'Пользователь не найден.';

            return Response::redirect('/admin/users');
        }

        $role = trim((string) $request->input('role', ''));
        $errors = [];

        if (!array_key_exists($role, User::roles())) {
            $errors['role'] = 'Выберите корректную роль.';
        }

        if ($errors !== []) {
            $_SESSION['users_edit_errors'] = $errors;
            $_SESSION['users_edit_old'] = ['role' => $role];

            return Response::redirect('/admin/users/edit?id=' . (int) $user->id);
        }

        $user->role = $role;
        $user->save();

        unset($_SESSION['users_edit_errors'], $_SESSION['users_edit_old']);
        $_SESSION['users_success'] = 'Роль пользователя обновлена.';

        return Response::redirect('/admin/users');
    }

    private function ensureAdmin(AuthService $auth): ?Response
    {
        if ($auth->guest()) {
            return Response::redirect('/login');
        }

        if (!$auth->hasRole('admin')) {
            $_SESSION['app_error'] = 'Доступ к управлению пользователями есть только у администратора.';

            return Response::redirect('/dashboard');
        }

        return null;
    }

    private function validateCreatePayload(array $payload): array
    {
        $errors = [];

        if ($payload['name'] === '') {
            $errors['name'] = 'Имя обязательно.';
        } elseif (strlen($payload['name']) < 2) {
            $errors['name'] = 'Имя должно быть не короче 2 символов.';
        }

        if ($payload['email'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректный email.';
        }

        if ($payload['password'] === '' || strlen($payload['password']) < 6) {
            $errors['password'] = 'Пароль должен быть не короче 6 символов.';
        }

        if (!array_key_exists($payload['role'], User::roles())) {
            $errors['role'] = 'Выберите корректную роль.';
        }

        return $errors;
    }
}

