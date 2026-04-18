<?php

namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public function agents()
    {
        return $this->hasMany(Agent::class, 'user_id');
    }
}