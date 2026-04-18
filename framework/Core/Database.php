<?php

namespace Yason\WebsiteTemplate\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {

            Env::load(ROOT . '/.env');

            $driver = Env::get('DB_DRIVER');
            $host   = Env::get('DB_HOST');
            $port   = Env::get('DB_PORT');
            $db     = Env::get('DB_DATABASE');
            $user   = Env::get('DB_USERNAME');
            $pass   = Env::get('DB_PASSWORD');

            $dsn = "$driver:host=$host;port=$port;dbname=$db";

            try {
                self::$connection = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}