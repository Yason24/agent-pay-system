<?php

return [

    'up' => function (PDO $db) {
        $db->exec('ALTER TABLE requests ADD COLUMN IF NOT EXISTS taken_by_user_id INT NULL');
        $db->exec('ALTER TABLE requests ADD COLUMN IF NOT EXISTS taken_by_name VARCHAR(255) NOT NULL DEFAULT \'\'');

        $db->exec('ALTER TABLE requests DROP CONSTRAINT IF EXISTS requests_taken_by_user_id_fk');
        $db->exec('ALTER TABLE requests ADD CONSTRAINT requests_taken_by_user_id_fk FOREIGN KEY (taken_by_user_id) REFERENCES users(id) ON DELETE SET NULL');

        $db->exec('CREATE INDEX IF NOT EXISTS idx_requests_taken_by_user_id ON requests(taken_by_user_id)');
    },

    'down' => function (PDO $db) {
        $db->exec('DROP INDEX IF EXISTS idx_requests_taken_by_user_id');
        $db->exec('ALTER TABLE requests DROP CONSTRAINT IF EXISTS requests_taken_by_user_id_fk');
        $db->exec('ALTER TABLE requests DROP COLUMN IF EXISTS taken_by_user_id');
    },

];

