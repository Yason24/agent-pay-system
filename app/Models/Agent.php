<?php

namespace App\Models;

use Framework\Core\Collection;
use Framework\Core\Model;
use App\Models\User;

class Agent extends Model
{
    protected static string $table = 'agents';

    public static array $sortable = ['id', 'name'];

    public static function forUser(int $userId): Collection
    {
        return static::where('user_id', '=', $userId)
            ->orderBy('id', 'DESC')
            ->get();
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}