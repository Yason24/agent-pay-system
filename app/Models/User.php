<?php

namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    private static ?bool $hasLoginColumnCache = null;

    public static array $sortable = [
        'id',
        'last_name',
        'first_name',
        'middle_name',
        'login',
        'phone',
        'email',
        'city',
        'role',
        'status',
        'created_at',
    ];

    public static function roles(): array
    {
        return [
            'admin' => 'Админ',
            'accountant' => 'Бухгалтер',
            'agent' => 'Агент',
            'dispatcher' => 'Диспетчер',
        ];
    }

    public static function roleLabel(?string $role): string
    {
        if ($role === null || $role === '') {
            return 'Гость';
        }

        if ($role === 'user') {
            return 'Агент';
        }

        return static::roles()[$role] ?? $role;
    }

    public static function statuses(): array
    {
        return [
            'active' => 'Активен',
            'blocked' => 'Заблокирован',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        if ($status === null || $status === '') {
            return static::statuses()['active'];
        }

        return static::statuses()[$status] ?? $status;
    }

    public static function findByEmail(string $email): ?self
    {
        $stmt =
            \Framework\Core\Database::getConnection()->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $stmt->execute(['email' => trim($email)]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new static($data) : null;
    }

    public static function findByLogin(string $login): ?self
    {
        $normalized = trim($login);

        try {
            if (static::hasLoginColumn()) {
                $stmt = \Framework\Core\Database::getConnection()->prepare(
                    'SELECT * FROM users
                     WHERE LOWER(login) = LOWER(:login)
                        OR LOWER(email) = LOWER(:login)
                     LIMIT 1'
                );
                $stmt->execute(['login' => $normalized]);
            } else {
                $stmt = \Framework\Core\Database::getConnection()->prepare(
                    'SELECT * FROM users
                     WHERE LOWER(email) = LOWER(:login)
                        OR LOWER(name) = LOWER(:login)
                     LIMIT 1'
                );
                $stmt->execute(['login' => $normalized]);
            }
        } catch (\PDOException $e) {
            if ((string) $e->getCode() !== '42703') {
                throw $e;
            }

            static::$hasLoginColumnCache = false;

            $stmt = \Framework\Core\Database::getConnection()->prepare(
                'SELECT * FROM users
                 WHERE LOWER(email) = LOWER(:login)
                    OR LOWER(name) = LOWER(:login)
                 LIMIT 1'
            );
            $stmt->execute(['login' => $normalized]);
        }

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new static($data) : null;
    }

    public static function findByUserLogin(string $login): ?self
    {
        if (!static::hasLoginColumn()) {
            return null;
        }

        $stmt = \Framework\Core\Database::getConnection()->prepare(
            'SELECT * FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1'
        );
        $stmt->execute(['login' => trim($login)]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new static($data) : null;
    }

    public static function findByPhone(string $phone): ?self
    {
        $normalized = trim($phone);

        if ($normalized === '') {
            return null;
        }

        $stmt = \Framework\Core\Database::getConnection()->prepare(
            "SELECT * FROM users WHERE NULLIF(BTRIM(phone), '') = :phone LIMIT 1"
        );
        $stmt->execute(['phone' => $normalized]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new static($data) : null;
    }

    public static function agents()
    {
        return static::where('role', '=', 'agent');
    }

    public static function findAgentById(int $id): ?self
    {
        if ($id <= 0) {
            return null;
        }

        return static::where('id', '=', $id)
            ->where('role', '=', 'agent')
            ->first();
    }

    public function fullName(): string
    {
        return static::composeFullName($this->attributes);
    }

    public static function composeFullName(array $user): string
    {
        $parts = [
            trim((string) ($user['last_name'] ?? '')),
            trim((string) ($user['first_name'] ?? '')),
            trim((string) ($user['middle_name'] ?? '')),
        ];

        $fullName = trim(implode(' ', array_filter($parts, static fn (string $value): bool => $value !== '')));

        if ($fullName !== '') {
            return $fullName;
        }

        return trim((string) ($user['name'] ?? ''));
    }

    private static function hasLoginColumn(): bool
    {
        if (static::$hasLoginColumnCache !== null) {
            return static::$hasLoginColumnCache;
        }

        try {
            $stmt = \Framework\Core\Database::getConnection()->prepare(
                "SELECT 1
                 FROM information_schema.columns
                 WHERE table_schema = 'public'
                   AND table_name = 'users'
                   AND column_name = 'login'
                 LIMIT 1"
            );
            $stmt->execute();

            static::$hasLoginColumnCache = (bool) $stmt->fetchColumn();
        } catch (\Throwable) {
            static::$hasLoginColumnCache = false;
        }

        return static::$hasLoginColumnCache;
    }
}