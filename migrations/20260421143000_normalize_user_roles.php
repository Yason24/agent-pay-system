<?php

return [

    'up' => function (PDO $db) {
        $db->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50)');
        $db->exec("UPDATE users SET role = 'agent' WHERE role IS NULL OR TRIM(role) = '' OR role = 'user'");
        $db->exec("UPDATE users SET role = 'agent' WHERE role NOT IN ('admin','accountant','agent','dispatcher')");
        $db->exec("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'agent'");
        $db->exec('ALTER TABLE users ALTER COLUMN role SET NOT NULL');
        $db->exec('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_allowed_check');
        $db->exec("ALTER TABLE users ADD CONSTRAINT users_role_allowed_check CHECK (role IN ('admin','accountant','agent','dispatcher'))");
    },

    'down' => function (PDO $db) {
        $db->exec('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_allowed_check');
        $db->exec('ALTER TABLE users ALTER COLUMN role DROP NOT NULL');
        $db->exec('ALTER TABLE users ALTER COLUMN role DROP DEFAULT');
    },

];

