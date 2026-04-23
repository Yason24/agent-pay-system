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

        $searchQuery = (string) ($_GET['q'] ?? '');
        $q = $this->normalizeSearch($searchQuery);
        $users = User::query()->orderBy('id', 'DESC')->get();

        if ($q !== '') {
            $users = $users->filter(function (User $user) use ($q): bool {
                $fullName = User::composeFullName([
                    'last_name' => (string) $user->last_name,
                    'first_name' => (string) $user->first_name,
                    'middle_name' => (string) $user->middle_name,
                    'name' => (string) $user->name,
                ]);

                $haystack = $this->normalizeSearch($fullName . ' ' . (string) $user->login);

                return str_contains($haystack, $q);
            });
        }

        return $this->view('admin.users.index', [
            'title' => 'Пользователи',
            'users' => $users,
            'success' => $success,
            'error' => $error,
            'search_query' => $searchQuery,
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

        $errors = $this->validateCreatePayload($payload);

        if (User::findByUserLogin($payload['login'])) {
            $errors['login'] = 'Логин уже используется.';
        }

        if (!isset($errors['phone']) && $payload['phone'] !== '') {
            $existingByPhone = User::findByPhone($payload['phone']);

            if ($existingByPhone !== null) {
                $errors['phone'] = 'Телефон уже используется.';
            }
        }

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
            'middle_name' => $payload['middle_name'],
            'login' => $payload['login'],
            'phone' => $payload['phone'],
            'city' => $payload['city'],
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

        $errors = $this->validateProfilePayload($payload, false);

        $existingByLogin = User::findByUserLogin($payload['login']);
        if ($existingByLogin !== null && (int) $existingByLogin->id !== (int) $user->id) {
            $errors['login'] = 'Логин уже используется.';
        }

        if (!isset($errors['phone']) && $payload['phone'] !== '') {
            $existingByPhone = User::findByPhone($payload['phone']);

            if ($existingByPhone !== null && (int) $existingByPhone->id !== (int) $user->id) {
                $errors['phone'] = 'Телефон уже используется.';
            }
        }

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
        $user->middle_name = $payload['middle_name'];
        $user->login = $payload['login'];
        $user->phone = $payload['phone'];
        $user->city = $payload['city'];
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

        $_SESSION['users_success'] = 'Пароль пользователя обновлен.';

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

    private function validateCreatePayload(array $payload): array
    {
        $errors = $this->validateProfilePayload($payload, true);

        if ($payload['password'] === '' || strlen($payload['password']) < 6) {
            $errors['password'] = 'Пароль должен быть не короче 6 символов.';
        }

        return $errors;
    }

    private function validateProfilePayload(array $payload, bool $requireStatus): array
    {
        $errors = [];

        if ($payload['last_name'] === '') {
            $errors['last_name'] = 'Фамилия обязательна.';
        } elseif (!$this->isValidNamePart($payload['last_name'])) {
            $errors['last_name'] = 'Некорректный формат фамилии.';
        }

        if ($payload['first_name'] === '') {
            $errors['first_name'] = 'Имя обязательно.';
        } elseif (!$this->isValidNamePart($payload['first_name'])) {
            $errors['first_name'] = 'Некорректный формат имени.';
        }

        if ($payload['middle_name'] !== '' && !$this->isValidNamePart($payload['middle_name'])) {
            $errors['middle_name'] = 'Некорректный формат отчества.';
        }

        if ($payload['login'] === '') {
            $errors['login'] = 'Логин обязателен.';
        } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $payload['login'])) {
            $errors['login'] = 'Логин может содержать только латинские буквы, цифры и символы . _ - (3-50 знаков).';
        }

        if ($payload['email'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректную эл. почту.';
        }

        if (!in_array($payload['role'], ['agent', 'dispatcher', 'accountant', 'admin'], true)) {
            $errors['role'] = 'Выберите корректную роль.';
        }

        if ($requireStatus || array_key_exists('status', $payload)) {
            if (!in_array($payload['status'], ['active', 'blocked'], true)) {
                $errors['status'] = 'Выберите корректный статус.';
            }
        }

        return $errors;
    }

    private function isValidNamePart(string $value): bool
    {
        return (bool) preg_match('/^[\p{L}\s\-\']{2,100}$/u', $value);
    }

    private function composeDisplayName(array $payload): string
    {
        $name = User::composeFullName($payload);

        if ($name !== '') {
            return $name;
        }

        return (string) ($payload['login'] ?? '');
    }

    private function normalizeSearch(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value);
        }

        return strtolower($value);
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
            'city' => (string) $user->city,
            'email' => (string) $user->email,
            'role' => (string) $user->role,
            'status' => $status,
        ];
    }
}


