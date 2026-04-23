<?php

namespace App\Models;

use Framework\Core\Collection;
use Framework\Core\Database;
use Framework\Core\Model;

class Payment extends Model
{
    protected static string $table = 'payments';
    private static ?array $columnsCache = null;

    public static array $sortable = ['id', 'payment_date', 'amount', 'status'];

    public static function forAgentUser(int $agentUserId): Collection
    {
        if ($agentUserId <= 0) {
            return new Collection([]);
        }

        return static::where('agent_user_id', '=', $agentUserId)
            ->orderBy('payment_date', 'DESC')
            ->get();
    }

    public static function findForAgentUser(int $paymentId, int $agentUserId): ?self
    {
        if ($paymentId <= 0 || $agentUserId <= 0) {
            return null;
        }

        return static::where('id', '=', $paymentId)
            ->where('agent_user_id', '=', $agentUserId)
            ->first();
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

        $payments = static::forAgentUser($agentUserId);

        $summary = [
            'total_amount' => 0.0,
            'paid_amount' => 0.0,
            'pending_amount' => 0.0,
            'failed_amount' => 0.0,
            'payments_count' => $payments->count(),
        ];

        foreach ($payments as $payment) {
            $amount = (float) $payment->amount;
            $summary['total_amount'] += $amount;

            if ((string) $payment->status === 'paid') {
                $summary['paid_amount'] += $amount;
            } elseif ((string) $payment->status === 'pending') {
                $summary['pending_amount'] += $amount;
            } elseif ((string) $payment->status === 'failed') {
                $summary['failed_amount'] += $amount;
            }
        }

        return [
            'total_amount' => (float) $summary['total_amount'],
            'paid_amount' => (float) $summary['paid_amount'],
            'pending_amount' => (float) $summary['pending_amount'],
            'failed_amount' => (float) $summary['failed_amount'],
            'payments_count' => (int) $summary['payments_count'],
        ];
    }

    public static function latestForAgentUser(int $agentUserId, int $limit = 5): Collection
    {
        if ($agentUserId <= 0 || $limit <= 0) {
            return new Collection([]);
        }

        return static::where('agent_user_id', '=', $agentUserId)
            ->orderBy('payment_date', 'DESC')
            ->limit($limit)
            ->get();
    }

    public static function createForAgentUser(int $agentUserId, array $attributes): ?self
    {
        if ($agentUserId <= 0) {
            return null;
        }

        return static::create([
            'agent_user_id' => $agentUserId,
            'amount' => $attributes['amount'],
            'payment_date' => $attributes['payment_date'],
            'status' => $attributes['status'],
            'note' => $attributes['note'] ?? null,
        ]);
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

    public static function historyRowsForAgentUser(int $agentUserId): array
    {
        if ($agentUserId <= 0) {
            return [];
        }

        try {
            $db = Database::getConnection();
            $columns = self::columns();

            if (in_array('created_at', $columns, true)) {
                $orderDateColumn = 'p.created_at';
                $dateExpr = 'p.created_at';
            } elseif (in_array('payment_date', $columns, true)) {
                $orderDateColumn = 'p.payment_date';
                $dateExpr = "(p.payment_date::text || ' 00:00:00')";
            } else {
                $orderDateColumn = 'p.id';
                $dateExpr = "''";
            }

            $typeExpr = in_array('type', $columns, true) ? 'p.type' : "'accrual'";

            if (in_array('comment', $columns, true)) {
                $commentExpr = 'p.comment';
            } elseif (in_array('note', $columns, true)) {
                $commentExpr = 'p.note';
            } else {
                $commentExpr = "''";
            }

            $statusExpr = in_array('status', $columns, true) ? 'p.status' : "'completed'";
            $relatedRequestExpr = in_array('related_request_id', $columns, true)
                ? 'p.related_request_id'
                : 'NULL';

            $actorNameExpr = "'—'";

            if (in_array('created_by_name', $columns, true)) {
                $actorNameExpr = "COALESCE(NULLIF(p.created_by_name, ''), '—')";
            } elseif (in_array('actor_name', $columns, true)) {
                $actorNameExpr = "COALESCE(NULLIF(p.actor_name, ''), '—')";
            } elseif (in_array('created_by_user_id', $columns, true)) {
                $actorNameExpr = "COALESCE(NULLIF(u.name, ''), '—')";
            }

            $join = in_array('created_by_user_id', $columns, true)
                ? ' LEFT JOIN users u ON u.id = p.created_by_user_id '
                : '';

            $sql = "SELECT
                    p.id,
                    {$dateExpr} AS operation_date,
                    {$typeExpr} AS operation_type,
                    p.amount,
                    {$statusExpr} AS operation_status,
                    {$actorNameExpr} AS actor_name,
                    {$commentExpr} AS operation_comment,
                    {$relatedRequestExpr} AS related_request_id
                FROM payments p
                {$join}
                WHERE p.agent_user_id = :agent_user_id
                ORDER BY {$orderDateColumn} DESC, p.id DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute(['agent_user_id' => $agentUserId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }

        $result = [];

        foreach ($rows as $row) {
            $typeRaw = (string) ($row['operation_type'] ?? 'accrual');

            $type = match ($typeRaw) {
                'adjustment' => 'Корректировка',
                'payout' => 'Выплата',
                default => 'Начисление',
            };

            $actorName = trim((string) ($row['actor_name'] ?? ''));
            $comment = trim((string) ($row['operation_comment'] ?? ''));

            $result[] = [
                'date' => (string) ($row['operation_date'] ?? ''),
                'type' => $type,
                'amount' => (float) ($row['amount'] ?? 0),
                'status' => (string) ($row['operation_status'] ?? 'completed'),
                'actor_name' => $actorName !== '' ? $actorName : '—',
                'comment' => $comment !== '' ? $comment : '—',
                'source' => 'payment',
                'source_id' => (int) ($row['id'] ?? 0),
                'related_request_id' => isset($row['related_request_id']) ? (int) $row['related_request_id'] : null,
            ];
        }

        return $result;
    }

    private static function columns(): array
    {
        if (is_array(static::$columnsCache)) {
            return static::$columnsCache;
        }

        try {
            $stmt = Database::getConnection()->query(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema = 'public' AND table_name = 'payments'"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            static::$columnsCache = array_map('strval', is_array($rows) ? $rows : []);
        } catch (\Throwable) {
            static::$columnsCache = [];
        }

        return static::$columnsCache;
    }
}

