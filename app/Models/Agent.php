<?php

namespace App\Models;

use Framework\Core\Model;   // ← ДОБАВИТЬ
use App\Models\User;

class Agent extends Model
{
    protected static string $table = 'agents';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}