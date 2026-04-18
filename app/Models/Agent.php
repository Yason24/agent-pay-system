<?php

namespace Yason\WebsiteTemplate\Models;

use Yason\WebsiteTemplate\Core\Model;   // ← ДОБАВИТЬ
use Yason\WebsiteTemplate\Models\User;

class Agent extends Model
{
    protected static string $table = 'agents';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}