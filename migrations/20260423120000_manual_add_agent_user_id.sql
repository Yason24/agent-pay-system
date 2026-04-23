BEGIN;

-- payments.agent_user_id
ALTER TABLE payments
    ADD COLUMN IF NOT EXISTS agent_user_id INT;

UPDATE payments p
SET agent_user_id = a.user_id
FROM agents a
WHERE p.agent_id = a.id
  AND p.agent_user_id IS NULL;

ALTER TABLE payments
    ALTER COLUMN agent_user_id SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_payments_agent_user_id ON payments(agent_user_id);

-- payment_requests.agent_user_id (only if table exists)
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = 'public'
          AND table_name = 'payment_requests'
    ) THEN
        EXECUTE 'ALTER TABLE payment_requests ADD COLUMN IF NOT EXISTS agent_user_id INT';

        EXECUTE '
            UPDATE payment_requests r
            SET agent_user_id = a.user_id
            FROM agents a
            WHERE r.agent_id = a.id
              AND r.agent_user_id IS NULL
        ';

        EXECUTE 'ALTER TABLE payment_requests ALTER COLUMN agent_user_id SET NOT NULL';
        EXECUTE 'CREATE INDEX IF NOT EXISTS idx_requests_agent_user_id ON payment_requests(agent_user_id)';
    END IF;
END $$;

COMMIT;

