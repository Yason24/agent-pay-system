<?php

return [

    'up' => function (PDO $db) {
        $db->exec('ALTER TABLE agents ADD COLUMN IF NOT EXISTS user_id INT');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_agents_user_id ON agents(user_id)');
        $db->exec('ALTER TABLE agents ADD CONSTRAINT agents_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    },

    'down' => function (PDO $db) {
        $db->exec('ALTER TABLE agents DROP CONSTRAINT IF EXISTS agents_user_id_fk');
        $db->exec('DROP INDEX IF EXISTS idx_agents_user_id');
        $db->exec('ALTER TABLE agents DROP COLUMN IF EXISTS user_id');
    },

];


