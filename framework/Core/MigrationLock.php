<?php

namespace Framework\Core;

use PDO;

class MigrationLock
{
    public static function lock(PDO $db): void
    {
        $db->exec("SELECT pg_advisory_lock(99999)");
    }

    public static function unlock(PDO $db): void
    {
        $db->exec("SELECT pg_advisory_unlock(99999)");
    }
}