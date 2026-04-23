<?php

namespace App\Models;

use App\Models\Request as AgentRequest;
use Framework\Core\Collection;
use Framework\Core\Model;

class Payment extends Model
{
    protected static string $table = 'payments';

    public static array $sortable = ['id', 'agent_user_id', 'payment_date', 'created_at', 'updated_at', 'amount', 'status', 'type'];

    public static function forAgentUser(int $agentUserId): Collection
    {
        if ($agentUserId <= 0) {
            return new Collection([]);
        }

        return static::where('agent_user_id', '=', $agentUserId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
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

    public static function balanceSummaryForAgentUser(int $agentUserId): array
    {
        if ($agentUserId <= 0) {
            return [
                'total' => 0.0,
                'reserved' => 0.0,
                'available' => 0.0,
            ];
        }

        $total = 0.0;

        foreach (static::forAgentUser($agentUserId) as $payment) {
            $type = strtolower(trim((string) $payment->type));
            if ($type === '') {
                $type = 'accrual';
            }

            $amount = (float) $payment->amount;

            if (in_array($type, ['accrual', 'adjustment'], true) && $amount > 0) {
                $total += $amount;
            }
        }

        $reserved = AgentRequest::reservedAmountForAgentUser($agentUserId);

        return [
            'total' => $total,
            'reserved' => $reserved,
            'available' => $total - $reserved,
        ];
    }

    public static function unifiedHistoryForAgentUser(int $agentUserId): array
    {
        if ($agentUserId <= 0) {
            return [];
        }

        $history = [];

        foreach (static::forAgentUser($agentUserId) as $payment) {
            $type = strtolower(trim((string) $payment->type));

            if ($type === '') {
                $type = 'accrual';
            }

            $typeLabel = match ($type) {
                'adjustment' => 'Корректировка',
                'payout' => 'Списание',
                default => 'Начисление',
            };

            $history[] = [
                'date' => (string) ($payment->created_at ?: $payment->payment_date),
                'type' => $typeLabel,
                'amount' => (float) $payment->amount,
                'status' => self::historyStatusLabel((string) $payment->status),
                'actor_name' => self::paymentActorName($payment),
                'comment' => trim((string) ($payment->comment !== null ? $payment->comment : $payment->note)),
                'sort_date' => (string) ($payment->created_at ?: $payment->payment_date),
                'source_id' => (int) $payment->id,
            ];
        }

        foreach (AgentRequest::paidHistoryRowsForAgentUser($agentUserId) as $requestRow) {
            $history[] = [
                'date' => (string) ($requestRow['date'] ?? ''),
                'type' => 'Заявка оплачена',
                'amount' => (float) ($requestRow['amount'] ?? 0),
                'status' => 'оплачено',
                'actor_name' => (string) ($requestRow['actor_name'] ?? '—'),
                'comment' => (string) ($requestRow['comment'] ?? ''),
                'sort_date' => (string) ($requestRow['date'] ?? ''),
                'source_id' => (int) ($requestRow['source_id'] ?? 0),
            ];
        }

        usort($history, static function (array $a, array $b): int {
            $dateCompare = strcmp((string) ($b['sort_date'] ?? ''), (string) ($a['sort_date'] ?? ''));

            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return ((int) ($b['source_id'] ?? 0)) <=> ((int) ($a['source_id'] ?? 0));
        });

        return $history;
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

    private static function historyStatusLabel(string $status): string
    {
        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'pending' => 'ожидает',
            'paid', 'done' => 'оплачено',
            default => $status,
        };
    }

    private static function paymentActorName(self $payment): string
    {
        $actorName = trim((string) ($payment->created_by_name !== null ? $payment->created_by_name : $payment->actor_name));

        if ($actorName !== '') {
            return $actorName;
        }

        return '—';
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
}

