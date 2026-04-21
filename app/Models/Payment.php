<?php

namespace App\Models;

use Framework\Core\Collection;
use Framework\Core\Database;
use Framework\Core\Model;
use PDO;

class Payment extends Model
{
    protected static string $table = 'payments';

    public static array $sortable = ['id', 'payment_date', 'amount', 'status'];

    public static function forAgentAndUser(int $agentId, int $userId): Collection
    {
        if ($agentId <= 0 || $userId <= 0) {
            return new Collection([]);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT p.*
             FROM payments p
             INNER JOIN agents a ON a.id = p.agent_id
             WHERE p.agent_id = :agent_id AND a.user_id = :user_id
             ORDER BY p.payment_date DESC, p.id DESC'
        );

        $stmt->execute([
            'agent_id' => $agentId,
            'user_id' => $userId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new Collection(array_map(
            fn(array $row) => new static($row),
            $rows
        ));
    }

    public static function findForUser(int $id, int $userId): ?self
    {
        if ($id <= 0 || $userId <= 0) {
            return null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT p.*
             FROM payments p
             INNER JOIN agents a ON a.id = p.agent_id
             WHERE p.id = :id AND a.user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new static($row) : null;
    }

    public static function summaryForAgentAndUser(int $agentId, int $userId): array
    {
        $empty = [
            'total_amount' => 0.0,
            'paid_amount' => 0.0,
            'pending_amount' => 0.0,
            'failed_amount' => 0.0,
            'payments_count' => 0,
        ];

        if ($agentId <= 0 || $userId <= 0) {
            return $empty;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(<<<'SQL'
SELECT
    COALESCE(SUM(p.amount), 0) AS total_amount,
    COALESCE(SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END), 0) AS paid_amount,
    COALESCE(SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END), 0) AS pending_amount,
    COALESCE(SUM(CASE WHEN p.status = 'failed' THEN p.amount ELSE 0 END), 0) AS failed_amount,
    COUNT(p.id) AS payments_count
FROM payments p
INNER JOIN agents a ON a.id = p.agent_id
WHERE p.agent_id = :agent_id AND a.user_id = :user_id
SQL
        );

        $stmt->execute([
            'agent_id' => $agentId,
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return $empty;
        }

        return [
            'total_amount' => (float) ($row['total_amount'] ?? 0),
            'paid_amount' => (float) ($row['paid_amount'] ?? 0),
            'pending_amount' => (float) ($row['pending_amount'] ?? 0),
            'failed_amount' => (float) ($row['failed_amount'] ?? 0),
            'payments_count' => (int) ($row['payments_count'] ?? 0),
        ];
    }

    public static function latestForAgentAndUser(int $agentId, int $userId, int $limit = 5): Collection
    {
        if ($agentId <= 0 || $userId <= 0 || $limit <= 0) {
            return new Collection([]);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT p.*
             FROM payments p
             INNER JOIN agents a ON a.id = p.agent_id
             WHERE p.agent_id = :agent_id AND a.user_id = :user_id
             ORDER BY p.payment_date DESC, p.id DESC
             LIMIT ' . (int) $limit
        );

        $stmt->execute([
            'agent_id' => $agentId,
            'user_id' => $userId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new Collection(array_map(
            fn(array $row) => new static($row),
            $rows
        ));
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}

