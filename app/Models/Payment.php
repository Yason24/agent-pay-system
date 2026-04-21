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

    public static function findAccessible(
        int $paymentId,
        int $currentUserId,
        bool $isAdmin = false,
        ?int $agentUserId = null
    ): ?self {
        if ($paymentId <= 0 || $currentUserId <= 0) {
            return null;
        }

        if ($isAdmin) {
            return static::findForAgentUser($paymentId, (int) $agentUserId);
        }

        return static::findForAgentUser($paymentId, $currentUserId);
    }
}
