<?php

return [

    'up' => function (PDO $db) {
        $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    agent_id INT NOT NULL,
    amount NUMERIC(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT payments_amount_positive_check CHECK (amount > 0),
    CONSTRAINT payments_status_allowed_check CHECK (status IN ('pending','paid','failed')),
    CONSTRAINT payments_agent_id_fk FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
)
SQL);

        $db->exec('CREATE INDEX IF NOT EXISTS idx_payments_agent_id ON payments(agent_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_payments_payment_date ON payments(payment_date)');
    },

    'down' => function (PDO $db) {
        $db->exec('DROP TABLE IF EXISTS payments');
    },

];

