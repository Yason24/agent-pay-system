<?php

// Скрипт для создания таблицы requests в PostgreSQL
// Используется как миграция вручную

require __DIR__ . '/vendor/autoload.php';

use Framework\Core\Database;

try {
    $db = Database::getConnection();

    echo "Проверяю таблицу requests...\n";

    // Проверяем, существует ли таблица
    $check = $db->query(
        "SELECT EXISTS (
            SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'public' AND table_name = 'requests'
        )"
    )->fetchColumn();

    if ($check) {
        echo "✓ Таблица requests уже существует.\n";

        // Показываем колонки
        $columns = $db->query(
            "SELECT column_name, data_type 
             FROM information_schema.columns 
             WHERE table_schema = 'public' AND table_name = 'requests'
             ORDER BY ordinal_position"
        )->fetchAll(\PDO::FETCH_ASSOC);

        echo "\nКолонки:\n";
        foreach ($columns as $col) {
            echo "  - {$col['column_name']} ({$col['data_type']})\n";
        }
    } else {
        echo "✗ Таблица requests не найдена. Создаю...\n";

        $db->exec("
            CREATE TABLE requests (
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
            )
        ");

        $db->exec("CREATE INDEX idx_requests_agent_user_id ON requests(agent_user_id)");
        $db->exec("CREATE INDEX idx_requests_status ON requests(status)");
        $db->exec("CREATE INDEX idx_requests_taken_by_user_id ON requests(taken_by_user_id)");

        echo "✓ Таблица requests создана успешно.\n";

        // Показываем созданные колонки
        $columns = $db->query(
            "SELECT column_name, data_type 
             FROM information_schema.columns 
             WHERE table_schema = 'public' AND table_name = 'requests'
             ORDER BY ordinal_position"
        )->fetchAll(\PDO::FETCH_ASSOC);

        echo "\nКолонки:\n";
        foreach ($columns as $col) {
            echo "  - {$col['column_name']} ({$col['data_type']})\n";
        }
    }

} catch (\Throwable $e) {
    echo "✗ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}


