<?php

namespace Seeders;

use App\Services\HashService;
use App\Models\User;
use PDO;

class UserSeeder
{
    public function run(PDO $db)
    {
        $hash = new HashService();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => $hash->make('12345'),
            'role' => 'admin'
        ]);
    }
}