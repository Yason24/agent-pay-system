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

            return redirect('/admin/users/create');
        }

        $createdUser = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $hash->make($payload['password']),
            'role' => $payload['role'],
            'status' => 'active',
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
            'name' => trim((string) $request->input('name', '')),
            'email' => trim((string) $request->input('email', '')),
            'role' => trim((string) $request->input('role', '')),
            'status' => trim((string) $request->input('status', 'active')),
        ];

        $errors = [];

        if ($payload['name'] === '') {
            $errors['name'] = 'Имя обязательно.';
        } elseif (strlen($payload['name']) < 2) {
            $errors['name'] = 'Имя должно быть не короче 2 символов.';
        }

        if ($payload['email'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректный email.';
        } else {
            $existing = User::findByEmail($payload['email']);

            if ($existing !== null && (int) $existing->id !== (int) $user->id) {
                $errors['email'] = 'Пользователь с таким email уже существует.';
            }
        }

        $allowedRoles = ['agent', 'dispatcher', 'accountant', 'admin'];
        $allowedStatuses = ['active', 'blocked'];

        if (!in_array($payload['role'], $allowedRoles, true)) {
            $errors['role'] = 'Выберите корректную роль.';
        }

        if (!in_array($payload['status'], $allowedStatuses, true)) {
            $errors['status'] = 'Выберите корректный статус.';
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

        $user->name = $payload['name'];
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

        if (!in_array($payload['role'], ['agent', 'dispatcher', 'accountant', 'admin'], true)) {
            $errors['role'] = 'Выберите корректную роль.';
        }

        return $errors;
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
            'email' => (string) $user->email,
            'role' => (string) $user->role,
            'status' => $status,
        ];
    }
}


