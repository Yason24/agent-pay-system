<?php

return [

    'up' => function (PDO $db) {
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_name VARCHAR(100)");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS first_name VARCHAR(100)");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS middle_name VARCHAR(100)");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS login VARCHAR(100)");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(50)");
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS city VARCHAR(120)");

        /** @noinspection SqlResolve */
        $db->exec("UPDATE users SET first_name = COALESCE(NULLIF(TRIM(name), ''), first_name)");

        /** @noinspection SqlResolve */
        $db->exec("UPDATE users SET login = 'user' || id WHERE login IS NULL OR TRIM(login) = ''");
        /** @noinspection SqlResolve */
        $db->exec("ALTER TABLE users ALTER COLUMN login SET NOT NULL");

        $db->exec('DROP INDEX IF EXISTS users_login_unique_idx');
        /** @noinspection SqlResolve */
        $db->exec('CREATE UNIQUE INDEX users_login_unique_idx ON users (LOWER(login))');
    },

    'down' => function (PDO $db) {
        $db->exec('DROP INDEX IF EXISTS users_login_unique_idx');
        /** @noinspection SqlResolve */
        $db->exec('ALTER TABLE users ALTER COLUMN login DROP NOT NULL');
        $db->exec('ALTER TABLE users DROP COLUMN IF EXISTS city');
        $db->exec('ALTER TABLE users DROP COLUMN IF EXISTS phone');
        $db->exec('ALTER TABLE users DROP COLUMN IF EXISTS login');
        $db->exec('ALTER TABLE users DROP COLUMN IF EXISTS middle_name');
        $db->exec('ALTER TABLE users DROP COLUMN IF EXISTS first_name');
        $db->exec('ALTER TABLE users DROP COLUMN IF EXISTS last_name');
    },

];


