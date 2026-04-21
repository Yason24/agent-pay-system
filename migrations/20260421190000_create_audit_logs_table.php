<?php

return [

    'up' => function (PDO $db) {
        $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS audit_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NULL,
    user_role VARCHAR(50) NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id INTEGER NULL,
    target_user_id INTEGER NULL,
    route VARCHAR(255) NULL,
    method VARCHAR(10) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'success',
    meta TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)
SQL);

        $db->exec('CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs (user_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs (action)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_audit_logs_entity ON audit_logs (entity_type, entity_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs (created_at)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_audit_logs_target_user_id ON audit_logs (target_user_id)');
    },

    'down' => function (PDO $db) {
        $db->exec('DROP TABLE IF EXISTS audit_logs');
    },

];

