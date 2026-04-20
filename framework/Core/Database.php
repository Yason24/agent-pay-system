<?php

namespace Framework\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $basePath = defined('BASE_PATH')
                ? BASE_PATH
                : dirname(__DIR__, 2);

            Env::load($basePath . '/.env');

            $driver = Env::get('DB_DRIVER');
            $host   = Env::get('DB_HOST');
            $port   = Env::get('DB_PORT');
            $db     = Env::get('DB_DATABASE');
            $user   = Env::get('DB_USERNAME');
            $pass   = Env::get('DB_PASSWORD');

            $availableDrivers = PDO::getAvailableDrivers();

            if (!in_array($driver, $availableDrivers, true)) {
                throw new RuntimeException('Configured database driver is not available.');
            }

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
                throw new RuntimeException('Database connection failed.', 0, $e);
            }
        }

        return self::$connection;
    }
}