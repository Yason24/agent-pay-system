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

    public function guest(): bool
    {
        return !$this->check();
    }

    public function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!$this->hash->verify($password, $user->password)) {
            return false;
        }

        $_SESSION[$this->sessionKey] = (int) $user->id;

        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[$this->sessionKey]);
    }
}