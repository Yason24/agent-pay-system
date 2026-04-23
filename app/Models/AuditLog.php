<?php

namespace App\Models;

use Framework\Core\Model;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    public static array $sortable = ['id', 'action', 'created_at', 'user_id', 'target_user_id'];

    public function save(): void
    {
        throw new \LogicException('AuditLog is append-only.');
    }

    public function delete(): void
    {
        throw new \LogicException('AuditLog is append-only.');
    }
}

