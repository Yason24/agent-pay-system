<?php

namespace Seeders;

use PDO;

class UserSeeder
{
    public function run(PDO $db)
    {
        $db->exec("
            INSERT INTO users (name,email,password,role)
            VALUES ('Admin','admin@test.com','123','admin')
        ");
    }
}