<?php

namespace App\Models;

use Framework\Core\Database;
use Framework\Core\Model;

class PaymentRequest extends Model
{
    protected static string $table = 'payment_requests';

    public static function paidHistoryRowsForAgentUser(int $agentUserId, array $excludeRequestIds = []): array
    {
        if ($agentUserId <= 0) {
            return [];
        }

        $tableName = static::resolveTableName();

        if ($tableName === null) {
            return [];
        }

        try {
            $db = Database::getConnection();
            $columns = static::columns($tableName);

            $dateExpr = in_array('updated_at', $columns, true)
                ? 'r.updated_at'
                : (in_array('created_at', $columns, true) ? 'r.created_at' : "''");

            $amountExpr = in_array('requested_amount', $columns, true)
                ? 'r.requested_amount'
                : (in_array('amount', $columns, true) ? 'r.amount' : '0');

            $commentExpr = in_array('comment', $columns, true) ? 'r.comment' : "''";

            $statusExpr = in_array('status', $columns, true) ? 'r.status' : "'paid'";

            $processedJoin = in_array('processed_by_user_id', $columns, true)
                ? ' LEFT JOIN users pu ON pu.id = r.processed_by_user_id '
                : '';

            $takenJoin = in_array('taken_by_user_id', $columns, true)
                ? ' LEFT JOIN users tu ON tu.id = r.taken_by_user_id '
                : '';

            $actorCandidates = [];

            if (in_array('processed_by_name', $columns, true)) {
                $actorCandidates[] = "NULLIF(r.processed_by_name, '')";
            }

            if (in_array('taken_by_name', $columns, true)) {
                $actorCandidates[] = "NULLIF(r.taken_by_name, '')";
            }

            if (in_array('processed_by_user_id', $columns, true)) {
                $actorCandidates[] = "NULLIF(pu.name, '')";
            }

            if (in_array('taken_by_user_id', $columns, true)) {
                $actorCandidates[] = "NULLIF(tu.name, '')";
            }

            $actorExpr = $actorCandidates === []
                ? "'—'"
                : 'COALESCE(' . implode(', ', $actorCandidates) . ", '—')";

            $params = [
                'agent_user_id' => $agentUserId,
                'paid_status' => 'paid',
            ];

            $excludeSql = '';
            $excludeRequestIds = array_values(array_filter(array_map('intval', $excludeRequestIds), static fn(int $id): bool => $id > 0));

            if ($excludeRequestIds !== []) {
                $placeholders = [];

                foreach ($excludeRequestIds as $index => $id) {
                    $key = 'exclude_' . $index;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $id;
                }

                $excludeSql = ' AND r.id NOT IN (' . implode(', ', $placeholders) . ')';
            }

            $sql = "SELECT
                    r.id,
                    {$dateExpr} AS operation_date,
                    {$amountExpr} AS operation_amount,
                    {$statusExpr} AS operation_status,
                    {$actorExpr} AS actor_name,
                    {$commentExpr} AS operation_comment
                FROM {$tableName} r
                {$processedJoin}
                {$takenJoin}
                WHERE r.agent_user_id = :agent_user_id
                  AND r.status = :paid_status
                  {$excludeSql}
                ORDER BY operation_date DESC, r.id DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }

        $result = [];

        foreach ($rows as $row) {
            $comment = trim((string) ($row['operation_comment'] ?? ''));
            $actorName = trim((string) ($row['actor_name'] ?? ''));

            $result[] = [
                'date' => (string) ($row['operation_date'] ?? ''),
                'type' => 'Заявка исполнена',
                'amount' => (float) ($row['operation_amount'] ?? 0),
                'status' => (string) ($row['operation_status'] ?? 'paid'),
                'actor_name' => $actorName !== '' ? $actorName : '—',
                'comment' => $comment !== '' ? $comment : '—',
                'source' => 'request',
                'source_id' => (int) ($row['id'] ?? 0),
+                'related_request_id' => (int) ($row['id'] ?? 0),
            ];
        }

        return $result;
    }

    private static function resolveTableName(): ?string
    {
        try {
            $db = Database::getConnection();

            $paymentRequests = $db->query("SELECT to_regclass('payment_requests')")->fetchColumn();

            if (!in_array($paymentRequests, [false, null, ''], true)) {
                return 'payment_requests';
            }

            $requests = $db->query("SELECT to_regclass('requests')")->fetchColumn();

            if (!in_array($requests, [false, null, ''], true)) {
                return 'requests';
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private static function columns(string $tableName): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema = 'public' AND table_name = :table_name"
            );
            $stmt->execute(['table_name' => $tableName]);

            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            return array_map('strval', is_array($rows) ? $rows : []);
        } catch (\Throwable) {
            return [];
        }
    }
}

