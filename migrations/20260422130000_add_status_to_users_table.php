<?php

return [

    'up' => function (PDO $db) {
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(50) NOT NULL DEFAULT 'active'");

        $db->exec("UPDATE users SET status = 'active' WHERE status IS NULL OR status = ''");

        $db->exec("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_allowed_check");
        $db->exec("ALTER TABLE users ADD CONSTRAINT users_status_allowed_check CHECK (status IN ('active','blocked'))");
    },

    'down' => function (PDO $db) {
        $db->exec('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_allowed_check');
        $db->exec('ALTER TABLE users DROP COLUMN IF EXISTS status');
    },

];

