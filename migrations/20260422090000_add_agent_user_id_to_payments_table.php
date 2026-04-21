<?php

return [

    'up' => function (PDO $db) {
        $db->exec('ALTER TABLE payments ADD COLUMN IF NOT EXISTS agent_user_id INT NULL');

        $db->exec(<<<'SQL'
UPDATE payments p
SET agent_user_id = a.user_id
FROM agents a
WHERE p.agent_user_id IS NULL
  AND p.agent_id = a.id
SQL
        );

        $db->exec('CREATE INDEX IF NOT EXISTS idx_payments_agent_user_id ON payments(agent_user_id)');
        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_agent_user_id_fk');
        $db->exec('ALTER TABLE payments ADD CONSTRAINT payments_agent_user_id_fk FOREIGN KEY (agent_user_id) REFERENCES users(id) ON DELETE CASCADE');
    },

    'down' => function (PDO $db) {
        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_agent_user_id_fk');
        $db->exec('DROP INDEX IF EXISTS idx_payments_agent_user_id');
        $db->exec('ALTER TABLE payments DROP COLUMN IF EXISTS agent_user_id');
    },

];

