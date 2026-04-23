CREATE TABLE IF NOT EXISTS requests (
    id               SERIAL PRIMARY KEY,
    agent_user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    requested_amount NUMERIC(12, 2) NOT NULL CHECK (requested_amount > 0),
    payment_link     TEXT NOT NULL DEFAULT '',
    comment          TEXT NOT NULL DEFAULT '',
    status           VARCHAR(32) NOT NULL DEFAULT 'new',
    taken_by_user_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    taken_by_name    VARCHAR(255) NOT NULL DEFAULT '',
    created_at       TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_requests_agent_user_id ON requests(agent_user_id);
CREATE INDEX IF NOT EXISTS idx_requests_status ON requests(status);
CREATE INDEX IF NOT EXISTS idx_requests_taken_by_user_id ON requests(taken_by_user_id);


