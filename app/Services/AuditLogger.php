<?php

namespace App\Services;

use App\Models\AuditLog;
use Framework\Core\Request;

class AuditLogger
{
    private array $forbiddenKeys = [
        'password',
        'password_hash',
        'hash',
        'csrf',
        'csrf_token',
        '_token',
        'session_id',
    ];

    public function log(string $action, Request $request, AuthService $auth, array $context = []): void
    {
        try {
            $user = $auth->user();

            $actorUserId = $context['actor_user_id'] ?? ($user !== null ? (int) $user->id : null);
            $actorUserRole = $context['actor_user_role'] ?? ($user !== null ? (string) $user->role : null);

            $meta = $this->sanitizeMeta($context['meta'] ?? null);

            AuditLog::create([
                'user_id' => $actorUserId,
                'user_role' => $actorUserRole,
                'action' => $action,
                'entity_type' => $context['entity_type'] ?? null,
                'entity_id' => $context['entity_id'] ?? null,
                'target_user_id' => $context['target_user_id'] ?? null,
                'route' => $request->uri(),
                'method' => $request->method(),
                'ip_address' => $this->ipAddress(),
                'user_agent' => $this->userAgent(),
                'status' => $context['status'] ?? 'success',
                'meta' => $meta !== null ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // audit log никогда не должен ломать основную бизнес-операцию
        }
    }

    public function diff(array $before, array $after): array
    {
        $changedFields = [];
        $old = [];
        $new = [];

        foreach ($after as $key => $value) {
            $beforeValue = $before[$key] ?? null;

            if ($beforeValue !== $value) {
                $changedFields[] = $key;
                $old[$key] = $beforeValue;
                $new[$key] = $value;
            }
        }

        return [
            'changed_fields' => $changedFields,
            'old' => $old,
            'new' => $new,
        ];
    }

    private function sanitizeMeta(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $key => $item) {
            $normalizedKey = is_string($key) ? strtolower($key) : $key;

            if (is_string($normalizedKey) && in_array($normalizedKey, $this->forbiddenKeys, true)) {
                continue;
            }

            if (is_array($item)) {
                $result[$key] = $this->sanitizeMeta($item);
                continue;
            }

            $result[$key] = $item;
        }

        return $result;
    }

    private function ipAddress(): ?string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        return is_string($ip) && $ip !== '' ? $ip : null;
    }

    private function userAgent(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        return is_string($userAgent) && $userAgent !== '' ? $userAgent : null;
    }
}

