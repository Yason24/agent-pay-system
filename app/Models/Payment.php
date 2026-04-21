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

    public static function forAgentUser(int $agentUserId): Collection
    {
        if ($agentUserId <= 0) {
            return new Collection([]);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT *
             FROM payments
             WHERE agent_user_id = :agent_user_id
             ORDER BY payment_date DESC, id DESC'
        );

        $stmt->execute([
            'agent_user_id' => $agentUserId,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new Collection(array_map(
            fn(array $row) => new static($row),
            $rows
        ));
    }

    public static function findForAgentUser(int $paymentId, int $agentUserId): ?self
    {
        if ($paymentId <= 0 || $agentUserId <= 0) {
            return null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT *
             FROM payments
             WHERE id = :id AND agent_user_id = :agent_user_id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $paymentId,
            'agent_user_id' => $agentUserId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new static($row) : null;
    }

    public static function summaryForAgentUser(int $agentUserId): array
    {
        if ($agentUserId <= 0) {
            return [
                'total_amount' => 0.0,
                'paid_amount' => 0.0,
                'pending_amount' => 0.0,
                'failed_amount' => 0.0,
                'payments_count' => 0,
            ];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT
                COUNT(*)::INT AS payments_count,
                COALESCE(SUM(amount), 0)::NUMERIC AS total_amount,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0)::NUMERIC AS paid_amount,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0)::NUMERIC AS pending_amount,
                COALESCE(SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END), 0)::NUMERIC AS failed_amount
             FROM payments
             WHERE agent_user_id = :agent_user_id"
        );

        $stmt->execute([
            'agent_user_id' => $agentUserId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_amount' => (float) ($row['total_amount'] ?? 0),
            'paid_amount' => (float) ($row['paid_amount'] ?? 0),
            'pending_amount' => (float) ($row['pending_amount'] ?? 0),
            'failed_amount' => (float) ($row['failed_amount'] ?? 0),
            'payments_count' => (int) ($row['payments_count'] ?? 0),
        ];
    }

    public static function latestForAgentUser(int $agentUserId, int $limit = 5): Collection
    {
        if ($agentUserId <= 0 || $limit <= 0) {
            return new Collection([]);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT *
             FROM payments
             WHERE agent_user_id = :agent_user_id
             ORDER BY payment_date DESC, id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':agent_user_id', $agentUserId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new Collection(array_map(
            fn(array $row) => new static($row),
            $rows
        ));
    }

    public static function findAccessible(
        int $paymentId,
        int $currentUserId,
        bool $isStaff = false,
        ?int $agentUserId = null
    ): ?self {
        if ($paymentId <= 0 || $currentUserId <= 0) {
            return null;
        }

        if ($isStaff) {
            $targetAgentUserId = (int) $agentUserId;

            if ($targetAgentUserId <= 0) {
                return null;
            }

            return static::findForAgentUser($paymentId, $targetAgentUserId);
        }

        return static::findForAgentUser($paymentId, $currentUserId);
    }
}

