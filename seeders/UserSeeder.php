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
            'name' => 'Admin User',
            'last_name' => 'Admin',
            'first_name' => 'User',
            'middle_name' => null,
            'login' => 'admin',
            'phone' => null,
            'email' => 'admin@test.com',
            'city' => null,
            'password' => $hash->make('12345'),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }
}