<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\AuthService;
use App\Services\HashService;
use App\Support\AuditAction;
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
            'statuses' => User::statuses(),
        ]);
    }

    public function store(Request $request, AuthService $auth, HashService $hash, AuditLogger $audit): Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $payload = [
            'last_name' => trim((string) $request->input('last_name', '')),
            'first_name' => trim((string) $request->input('first_name', '')),
            'middle_name' => trim((string) $request->input('middle_name', '')),
            'login' => trim((string) $request->input('login', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'city' => trim((string) $request->input('city', '')),
            'email' => trim((string) $request->input('email', '')),
            'password' => (string) $request->input('password', ''),
            'role' => trim((string) $request->input('role', 'agent')),
            'status' => trim((string) $request->input('status', 'active')),
        ];

        $errors = $this->validateUserPayload($payload, null, true);

        if ($errors !== []) {
            unset($payload['password']);
            $_SESSION['users_create_errors'] = $errors;
            $_SESSION['users_create_old'] = $payload;

            return redirect('/admin/users/create');
        }

        $createdUser = User::create([
            'name' => $this->composeDisplayName($payload),
            'last_name' => $payload['last_name'],
            'first_name' => $payload['first_name'],
            'middle_name' => $payload['middle_name'] !== '' ? $payload['middle_name'] : null,
            'login' => $payload['login'],
            'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
            'city' => $payload['city'] !== '' ? $payload['city'] : null,
            'email' => $payload['email'],
            'password' => $hash->make($payload['password']),
            'role' => $payload['role'],
            'status' => $payload['status'],
        ]);

        $audit->log(AuditAction::USER_CREATE, $request, $auth, [
            'entity_type' => 'user',
            'entity_id' => (int) $createdUser->id,
            'target_user_id' => (int) $createdUser->id,
            'meta' => [
                'snapshot' => $this->userSnapshot($createdUser),
            ],
        ]);

        unset($_SESSION['users_create_errors'], $_SESSION['users_create_old']);
        $_SESSION['users_success'] = 'Пользователь успешно создан.';

        return redirect('/admin/users');
    }

    public function edit(Request $request, AuthService $auth): string|Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $user = User::find((int) $request->input('id', 0));

        if ($user === null) {
            $_SESSION['users_error'] = 'Пользователь не найден.';

            return redirect('/admin/users');
        }

        $errors = $_SESSION['users_edit_errors'] ?? [];
        $old = $_SESSION['users_edit_old'] ?? [];

        unset($_SESSION['users_edit_errors'], $_SESSION['users_edit_old']);

        return $this->view('admin.users.edit', [
            'title' => 'Редактировать пользователя',
            'userRecord' => $user,
            'errors' => $errors,
            'old' => $old,
            'roles' => User::roles(),
            'statuses' => User::statuses(),
        ]);
    }

    public function update(Request $request, AuthService $auth, AuditLogger $audit): Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $user = User::find((int) $request->input('id', 0));

        if ($user === null) {
            $_SESSION['users_error'] = 'Пользователь не найден.';

            return redirect('/admin/users');
        }

        $payload = [
            'last_name' => trim((string) $request->input('last_name', '')),
            'first_name' => trim((string) $request->input('first_name', '')),
            'middle_name' => trim((string) $request->input('middle_name', '')),
            'login' => trim((string) $request->input('login', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'city' => trim((string) $request->input('city', '')),
            'email' => trim((string) $request->input('email', '')),
            'role' => trim((string) $request->input('role', '')),
            'status' => trim((string) $request->input('status', 'active')),
        ];

        $errors = $this->validateUserPayload($payload, (int) $user->id, false);

        if ($this->isLastAdminRoleDowngrade($user, $payload['role'])) {
            $errors['role'] = 'Нельзя изменить роль последнего администратора.';
        }

        if ($errors !== []) {
            $_SESSION['users_edit_errors'] = $errors;
            $_SESSION['users_edit_old'] = $payload;

            return redirect('/admin/users/edit?id=' . (int) $user->id);
        }

        $before = $this->userSnapshot($user);
        $oldRole = (string) $user->role;

        $user->name = $this->composeDisplayName($payload);
        $user->last_name = $payload['last_name'];
        $user->first_name = $payload['first_name'];
        $user->middle_name = $payload['middle_name'] !== '' ? $payload['middle_name'] : null;
        $user->login = $payload['login'];
        $user->phone = $payload['phone'] !== '' ? $payload['phone'] : null;
        $user->city = $payload['city'] !== '' ? $payload['city'] : null;
        $user->email = $payload['email'];
        $user->role = $payload['role'];
        $user->status = $payload['status'];
        $user->save();

        $after = $this->userSnapshot($user);
        $changes = $audit->diff($before, $after);

        $audit->log(AuditAction::USER_UPDATE, $request, $auth, [
            'entity_type' => 'user',
            'entity_id' => (int) $user->id,
            'target_user_id' => (int) $user->id,
            'meta' => $changes,
        ]);

        if ($oldRole !== $payload['role']) {
            $audit->log(AuditAction::USER_ROLE_CHANGED, $request, $auth, [
                'entity_type' => 'user',
                'entity_id' => (int) $user->id,
                'target_user_id' => (int) $user->id,
                'meta' => [
                    'changed_fields' => ['role'],
                    'old' => ['role' => $oldRole],
                    'new' => ['role' => $payload['role']],
                ],
            ]);
        }

        unset($_SESSION['users_edit_errors'], $_SESSION['users_edit_old']);
        $_SESSION['users_success'] = 'Пользователь успешно обновлен.';

        return redirect('/admin/users');
    }

    public function resetPassword(Request $request, AuthService $auth, HashService $hash, AuditLogger $audit): Response
    {
        if ($response = $this->ensureAdmin($auth)) {
            return $response;
        }

        $user = User::find((int) $request->input('id', 0));

        if ($user === null) {
            $_SESSION['users_error'] = 'Пользователь не найден.';

            return redirect('/admin/users');
        }

        $password = (string) $request->input('password', '');

        if ($password === '' || strlen($password) < 6) {
            $_SESSION['users_error'] = 'Новый пароль должен быть не короче 6 символов.';

            return redirect('/admin/users/edit?id=' . (int) $user->id);
        }

        $user->password = $hash->make($password);
        $user->save();

        $audit->log(AuditAction::USER_UPDATE, $request, $auth, [
            'entity_type' => 'user',
            'entity_id' => (int) $user->id,
            'target_user_id' => (int) $user->id,
            'meta' => [
                'changed_fields' => ['password'],
                'old' => ['password' => '[hidden]'],
                'new' => ['password' => '[hidden]'],
                'reason' => 'admin_reset_password',
            ],
        ]);

        $_SESSION['users_success'] = 'Пароль пользователя изменен.';

        return redirect('/admin/users/edit?id=' . (int) $user->id);
    }

    private function ensureAdmin(AuthService $auth): ?Response
    {
        if ($auth->guest()) {
            return redirect('/login');
        }

        if (!$auth->hasRole('admin')) {
            $_SESSION['app_error'] = 'Доступ к управлению пользователями есть только у администратора.';

            return redirect('/dashboard');
        }

        return null;
    }

    private function isLastAdminRoleDowngrade(User $user, string $newRole): bool
    {
        if ((string) $user->role !== 'admin') {
            return false;
        }

        if ($newRole === 'admin') {
            return false;
        }

        $adminsCount = User::where('role', '=', 'admin')->count();

        return $adminsCount <= 1;
    }

    private function validateUserPayload(array $payload, ?int $ignoreUserId = null, bool $withPassword = false): array
    {
        $errors = [];

        if (!$this->isValidName($payload['last_name'])) {
            $errors['last_name'] = 'Фамилия обязательна и может содержать только буквы, пробел и дефис.';
        }

        if (!$this->isValidName($payload['first_name'])) {
            $errors['first_name'] = 'Имя обязательно и может содержать только буквы, пробел и дефис.';
        }

        if ($payload['middle_name'] !== '' && !$this->isValidName($payload['middle_name'])) {
            $errors['middle_name'] = 'Отчество может содержать только буквы, пробел и дефис.';
        }

        if ($payload['login'] === '') {
            $errors['login'] = 'Логин обязателен.';
        } elseif (!preg_match('/^[A-Za-z0-9]+$/', $payload['login'])) {
            $errors['login'] = 'Логин должен содержать только латинские буквы и цифры.';
        } else {
            $existingByLogin = User::findByUserLogin($payload['login']);

            if ($existingByLogin !== null && (int) $existingByLogin->id !== (int) ($ignoreUserId ?? 0)) {
                $errors['login'] = 'Пользователь с таким логином уже существует.';
            }
        }

        if ($payload['email'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректный email.';
        } else {
            $existingByEmail = User::findByEmail($payload['email']);

            if ($existingByEmail !== null && (int) $existingByEmail->id !== (int) ($ignoreUserId ?? 0)) {
                $errors['email'] = 'Пользователь с таким email уже существует.';
            }
        }

        if ($withPassword && ($payload['password'] === '' || strlen($payload['password']) < 6)) {
            $errors['password'] = 'Пароль должен быть не короче 6 символов.';
        }

        if (!in_array($payload['role'], ['agent', 'dispatcher', 'accountant', 'admin'], true)) {
            $errors['role'] = 'Выберите корректную роль.';
        }

        if (!in_array($payload['status'], ['active', 'blocked'], true)) {
            $errors['status'] = 'Выберите корректный статус.';
        }

        return $errors;
    }

    private function isValidName(string $value): bool
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return false;
        }

        return (bool) preg_match('/^[\p{L}\s-]+$/u', $trimmed);
    }

    private function composeDisplayName(array $payload): string
    {
        $parts = [
            trim((string) ($payload['last_name'] ?? '')),
            trim((string) ($payload['first_name'] ?? '')),
            trim((string) ($payload['middle_name'] ?? '')),
        ];

        return trim(implode(' ', array_filter($parts, static fn (string $part): bool => $part !== '')));
    }

    private function userSnapshot(User $user): array
    {
        $status = (string) $user->status;

        if ($status === '') {
            $status = 'active';
        }

        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'last_name' => (string) $user->last_name,
            'first_name' => (string) $user->first_name,
            'middle_name' => (string) $user->middle_name,
            'login' => (string) $user->login,
            'phone' => (string) $user->phone,
            'email' => (string) $user->email,
            'city' => (string) $user->city,
            'role' => (string) $user->role,
            'status' => $status,
        ];
    }
}


