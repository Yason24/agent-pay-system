<?php

namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static array $sortable = ['id', 'name', 'email', 'role', 'created_at'];

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

    public static function findByEmail(string $email): ?self
    {
        return static::where('email', '=', $email)->first();
    }

    public static function findByLogin(string $login): ?self
    {
        $db = \Framework\Core\Database::getConnection();
        $stmt = $db->prepare(
            'SELECT * FROM users WHERE LOWER(email) = LOWER(:login) OR LOWER(name) = LOWER(:login) LIMIT 1'
        );
        $stmt->execute(['login' => $login]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new static($data) : null;
    }

    public function agents()
    {
        return $this->hasMany(Agent::class, 'user_id');
    }
}