<?php

namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?self
    {
        return static::where('email', '=', $email)->first();
    }

    public function agents()
    {
        return $this->hasMany(Agent::class, 'user_id');
    }
}