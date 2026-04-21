<?php

return [

    'up' => function (PDO $db) {
        $db->exec('ALTER TABLE payments ADD COLUMN IF NOT EXISTS agent_user_id INT NULL');

        $db->exec(<<<'SQL'
UPDATE payments p
SET agent_user_id = a.user_id
FROM agents a
WHERE a.id = p.agent_id
  AND p.agent_user_id IS NULL
SQL
        );

        $db->exec('CREATE INDEX IF NOT EXISTS idx_payments_agent_user_id ON payments(agent_user_id)');

        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_agent_user_id_fk');
        $db->exec('ALTER TABLE payments ADD CONSTRAINT payments_agent_user_id_fk FOREIGN KEY (agent_user_id) REFERENCES users(id) ON DELETE CASCADE');

        $nullCount = (int) $db->query('SELECT COUNT(*) FROM payments WHERE agent_user_id IS NULL')->fetchColumn();

        if ($nullCount === 0) {
            $db->exec('ALTER TABLE payments ALTER COLUMN agent_user_id SET NOT NULL');
        }
    },

    'down' => function (PDO $db) {
        $db->exec('ALTER TABLE payments ALTER COLUMN agent_user_id DROP NOT NULL');
        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_agent_user_id_fk');
        $db->exec('DROP INDEX IF EXISTS idx_payments_agent_user_id');
    },

];

