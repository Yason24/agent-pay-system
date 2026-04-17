<?php

namespace Yason\WebsiteTemplate\Core;

use PDO;

class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $dsn = sprintf(
            "%s:host=%s;port=%s;dbname=%s",
            Env::get('DB_DRIVER'),
            Env::get('DB_HOST'),
            Env::get('DB_PORT'),
            Env::get('DB_DATABASE')
        );

        $this->pdo = new PDO(
            $dsn,
            Env::get('DB_USERNAME'),
            Env::get('DB_PASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}