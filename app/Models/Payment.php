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

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}

