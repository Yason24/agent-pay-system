<?php

namespace App\Models;

use Framework\Core\Collection;
use Framework\Core\Database;
use Framework\Core\Model;

class Request extends Model
{
    protected static string $table = 'requests';
    private static ?array $columnsCache = null;
    private static ?string $lastCreateError = null;
    private static ?string $lastTakeError = null;

    public static array $statuses = [
        'new'        => 'Новая',
        'in_progress' => 'В работе',
        'in_work'    => 'В работе',
        'done'       => 'Выполнена',
        'rejected'   => 'Отклонена',
    ];

    public static function statusLabel(string $status): string
    {
        return static::$statuses[$status] ?? $status;
    }

    public static function forAgentUser(int $agentUserId): Collection
    {
        if ($agentUserId <= 0) {
            return new Collection([]);
        }

        try {
            $db = Database::getConnection();
            $columns = static::columns();
            $hasTakenByUserId = in_array('taken_by_user_id', $columns, true);
            $hasTakenByName = in_array('taken_by_name', $columns, true);
            $hasCreatedAt = in_array('created_at', $columns, true);

            if ($hasTakenByUserId) {
                $takenByExpr = $hasTakenByName
                    ? "COALESCE(NULLIF(r.taken_by_name, ''), u.name, '') AS taken_by_name"
                    : "COALESCE(u.name, '') AS taken_by_name";
                $join = ' LEFT JOIN users u ON u.id = r.taken_by_user_id ';
            } else {
                $takenByExpr = $hasTakenByName
                    ? "COALESCE(r.taken_by_name, '') AS taken_by_name"
                    : "'' AS taken_by_name";
                $join = '';
            }

            $orderBy = $hasCreatedAt ? ' ORDER BY r.created_at DESC, r.id DESC ' : ' ORDER BY r.id DESC ';
            $sql = "SELECT r.*, {$takenByExpr} FROM requests r {$join} WHERE r.agent_user_id = :agent_user_id{$orderBy}";

            $stmt = $db->prepare($sql);
            $stmt->execute(['agent_user_id' => $agentUserId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return new Collection(array_map(static fn(array $row) => new static($row), $rows));
        } catch (\Throwable $e) {
            error_log('Request::forAgentUser error: ' . $e->getMessage());
            return new Collection([]);
        }
    }

    public static function createForAgentUser(int $agentUserId, array $data): bool
    {
        static::$lastCreateError = null;

        if ($agentUserId <= 0) {
            static::$lastCreateError = 'Некорректный agent_user_id.';

            return false;
        }

        $now = date('Y-m-d H:i:s');
        $amount = (float) ($data['requested_amount'] ?? $data['amount'] ?? 0);
        $paymentLink = (string) ($data['payment_link'] ?? $data['link'] ?? '');
        $comment = (string) ($data['comment'] ?? '');
        $topic = (string) ($data['topic'] ?? 'Payment request');
        $status = (string) ($data['status'] ?? 'new');

        $columns = static::columns();
        $payload = [];

        // Базовые поля для обеих схем.
        foreach (['agent_user_id', 'status', 'comment', 'created_at', 'updated_at', 'taken_by_name'] as $field) {
            if (in_array($field, $columns, true)) {
                $payload[$field] = match ($field) {
                    'agent_user_id' => $agentUserId,
                    'status' => $status,
                    'comment' => $comment,
                    'created_at', 'updated_at' => $now,
                    'taken_by_name' => (string) ($data['taken_by_name'] ?? ''),
                };
            }
        }

        if (in_array('taken_by_user_id', $columns, true)) {
            $payload['taken_by_user_id'] = null;
        }

        // Fallback по сумме: requested_amount OR amount.
        if (in_array('requested_amount', $columns, true)) {
            $payload['requested_amount'] = $amount;
        }
        if (in_array('amount', $columns, true)) {
            $payload['amount'] = $amount;
        }

        // Fallback по ссылке: payment_link OR link.
        if (in_array('payment_link', $columns, true)) {
            $payload['payment_link'] = $paymentLink;
        }
        if (in_array('link', $columns, true)) {
            $payload['link'] = $paymentLink;
        }

        // Для старой схемы, где topic может быть NOT NULL.
        if (in_array('topic', $columns, true)) {
            $payload['topic'] = $topic;
        }

        if (!in_array('requested_amount', $columns, true) && !in_array('amount', $columns, true)) {
            static::$lastCreateError = 'В таблице requests нет поля суммы (requested_amount/amount).';

            return false;
        }

        if ($payload === []) {
            static::$lastCreateError = 'Не удалось сопоставить поля таблицы requests.';

            return false;
        }

        try {
            static::create($payload);
        } catch (\Throwable $e) {
            static::$lastCreateError = $e->getMessage();
            error_log('Request::createForAgentUser failed: ' . $e->getMessage());

            return false;
        }

        return true;
    }

    public static function lastCreateError(): ?string
    {
        return static::$lastCreateError;
    }

    public static function takeInProgress(int $requestId, int $actorUserId, string $actorName): bool
    {
        static::$lastTakeError = null;

        if ($requestId <= 0 || $actorUserId <= 0) {
            static::$lastTakeError = 'Некорректные параметры запроса.';

            return false;
        }

        try {
            $db = Database::getConnection();
            $columns = static::columns();

            if (!in_array('status', $columns, true)) {
                static::$lastTakeError = 'В таблице requests отсутствует поле status.';

                return false;
            }

            $setParts = ['status = :status'];
            $params = [
                'status' => 'in_progress',
                'request_id' => $requestId,
            ];

            if (in_array('updated_at', $columns, true)) {
                $setParts[] = 'updated_at = :updated_at';
                $params['updated_at'] = date('Y-m-d H:i:s');
            }

            if (in_array('taken_by_user_id', $columns, true)) {
                $setParts[] = 'taken_by_user_id = :taken_by_user_id';
                $params['taken_by_user_id'] = $actorUserId;
            }

            if (in_array('taken_by_name', $columns, true)) {
                $setParts[] = 'taken_by_name = :taken_by_name';
                $params['taken_by_name'] = $actorName;
            }

            $where = 'id = :request_id AND status = :new_status';
            $params['new_status'] = 'new';

            if (in_array('taken_by_user_id', $columns, true)) {
                $where .= ' AND (taken_by_user_id IS NULL OR taken_by_user_id = 0)';
            }

            $sql = 'UPDATE requests SET ' . implode(', ', $setParts) . ' WHERE ' . $where;
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 1) {
                return true;
            }

            $probe = $db->prepare('SELECT status FROM requests WHERE id = :id');
            $probe->execute(['id' => $requestId]);
            $current = $probe->fetchColumn();

            if ($current === false) {
                static::$lastTakeError = 'Заявка не найдена.';
            } elseif ((string) $current !== 'new') {
                static::$lastTakeError = 'Заявка уже взята в работу.';
            } else {
                static::$lastTakeError = 'Не удалось взять заявку в работу.';
            }

            return false;
        } catch (\Throwable $e) {
            static::$lastTakeError = $e->getMessage();
            error_log('Request::takeInProgress failed: ' . $e->getMessage());

            return false;
        }
    }

    public static function lastTakeError(): ?string
    {
        return static::$lastTakeError;
    }

    private static function columns(): array
    {
        if (is_array(static::$columnsCache)) {
            return static::$columnsCache;
        }

        try {
            $db = Database::getConnection();
            static::ensureSchema($db);

            $stmt = $db->query(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema = 'public' AND table_name = 'requests'
                 ORDER BY ordinal_position"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            static::$columnsCache = array_map('strval', is_array($rows) ? $rows : []);
        } catch (\Throwable $e) {
            error_log('Request::columns() error: ' . $e->getMessage());
            static::$columnsCache = [];
        }

        return static::$columnsCache;
    }

    private static function ensureSchema(\PDO $db): void
    {
        $tableExists = (bool) $db->query(
            "SELECT EXISTS (
                SELECT 1 FROM information_schema.tables
                WHERE table_schema = 'public' AND table_name = 'requests'
            )"
        )->fetchColumn();

        if (!$tableExists) {
            $db->exec(
                "CREATE TABLE IF NOT EXISTS requests (
                    id SERIAL PRIMARY KEY,
                    agent_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                    requested_amount NUMERIC(12, 2) NOT NULL CHECK (requested_amount > 0),
                    payment_link TEXT NOT NULL DEFAULT '',
                    comment TEXT NOT NULL DEFAULT '',
                    status VARCHAR(32) NOT NULL DEFAULT 'new',
                    taken_by_user_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
                    taken_by_name VARCHAR(255) NOT NULL DEFAULT '',
                    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
                    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
                )"
            );
            $db->exec("CREATE INDEX IF NOT EXISTS idx_requests_agent_user_id ON requests(agent_user_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_requests_status ON requests(status)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_requests_taken_by_user_id ON requests(taken_by_user_id)");

            return;
        }

        $hasTakenByUserId = (bool) $db->query(
            "SELECT EXISTS (
                SELECT 1
                FROM information_schema.columns
                WHERE table_schema = 'public' AND table_name = 'requests' AND column_name = 'taken_by_user_id'
            )"
        )->fetchColumn();

        if (!$hasTakenByUserId) {
            $db->exec('ALTER TABLE requests ADD COLUMN taken_by_user_id INTEGER NULL');
            $db->exec('ALTER TABLE requests DROP CONSTRAINT IF EXISTS requests_taken_by_user_id_fk');
            $db->exec('ALTER TABLE requests ADD CONSTRAINT requests_taken_by_user_id_fk FOREIGN KEY (taken_by_user_id) REFERENCES users(id) ON DELETE SET NULL');
            $db->exec('CREATE INDEX IF NOT EXISTS idx_requests_taken_by_user_id ON requests(taken_by_user_id)');
        }

        $hasTakenByName = (bool) $db->query(
            "SELECT EXISTS (
                SELECT 1
                FROM information_schema.columns
                WHERE table_schema = 'public' AND table_name = 'requests' AND column_name = 'taken_by_name'
            )"
        )->fetchColumn();

        if (!$hasTakenByName) {
            $db->exec("ALTER TABLE requests ADD COLUMN taken_by_name VARCHAR(255) NOT NULL DEFAULT ''");
        }
    }
}


