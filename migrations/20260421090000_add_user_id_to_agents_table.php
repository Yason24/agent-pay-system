<?php

return [

    'up' => function (PDO $db) {
        // 1. add nullable first so existing rows don't fail immediately
        $db->exec('ALTER TABLE agents ADD COLUMN IF NOT EXISTS user_id INT');

        // 2. clean legacy rows before NOT NULL/FK
        $db->exec('DELETE FROM agents WHERE user_id IS NULL');
        $db->exec('DELETE FROM agents WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)');

        // 3. enforce ownership at DB level
        $db->exec('ALTER TABLE agents ALTER COLUMN user_id SET NOT NULL');

        // 4. idempotent index + FK
        $db->exec('CREATE INDEX IF NOT EXISTS idx_agents_user_id ON agents(user_id)');
        $db->exec('ALTER TABLE agents DROP CONSTRAINT IF EXISTS agents_user_id_fk');
        $db->exec('ALTER TABLE agents ADD CONSTRAINT agents_user_id_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    },

    'down' => function (PDO $db) {
        $db->exec('ALTER TABLE agents DROP CONSTRAINT IF EXISTS agents_user_id_fk');
        $db->exec('DROP INDEX IF EXISTS idx_agents_user_id');
        $db->exec('ALTER TABLE agents DROP COLUMN IF EXISTS user_id');
    },

];



