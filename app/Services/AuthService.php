<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    protected string $sessionKey = 'auth_user_id';

    public function __construct(
        private HashService $hash
    ) {}

    public function user(): ?User
    {
        $userId = $_SESSION[$this->sessionKey] ?? null;

        if (!$userId) {
            return null;
        }

        return User::find((int) $userId);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function id(): ?int
    {
        $user = $this->user();

        return $user ? (int) $user->id : null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function hasRole(string $role): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return (string) $user->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return in_array((string) $user->role, $roles, true);
    }

    public function attempt(string $login, string $password): bool
    {
        $user = User::findByLogin($login);

        if (!$user) {
            return false;
        }

        if (!$this->hash->verify($password, $user->password)) {
            return false;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION[$this->sessionKey] = (int) $user->id;

        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[$this->sessionKey]);

        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                (bool) ($params['secure'] ?? false),
                (bool) ($params['httponly'] ?? false)
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}