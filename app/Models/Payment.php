<?php

namespace App\Models;

use Framework\Core\Collection;
use Framework\Core\Model;

class Payment extends Model
{
    protected static string $table = 'payments';

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
}

