<?php

namespace Yason\WebsiteTemplate\Core;

use PDO;

class SeederRunner
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function run(string $class)
    {
        $class = "Seeders\\$class";

        if (!class_exists($class)) {
            echo "Seeder not found\n";
            return;
        }

        $seeder = new $class;

        $seeder->run($this->db);

        echo "Seeder executed ✅\n";
    }
}