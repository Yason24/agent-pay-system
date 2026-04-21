<?php

namespace App\Models;

use Framework\Core\Collection;
use Framework\Core\Model;

class Request extends Model
{
    protected static string $table = 'requests';

    public static array $statuses = [
        'new'        => 'Новая',
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

        return static::where('agent_user_id', '=', $agentUserId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
    }

    public static function createForAgentUser(int $agentUserId, array $data): ?self
    {
        if ($agentUserId <= 0) {
            return null;
        }

        return static::create([
            'agent_user_id'    => $agentUserId,
            'requested_amount' => $data['requested_amount'],
            'payment_link'     => $data['payment_link'] ?? '',
            'comment'          => $data['comment'] ?? '',
            'status'           => $data['status'] ?? 'new',
            'taken_by_name'    => '',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
    }
}

