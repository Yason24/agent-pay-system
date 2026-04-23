<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array<string, mixed> $summary */ ?>
<?php /** @var array<int, array<string, mixed>> $history */ ?>
<?php /** @var bool $isAgentMode */ ?>
<?php /** @var int $agentUserId */ ?>
@extends('layouts.app')

@section('content')
<section>
    <style>
        .history-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .history-type-accrual { background: #ecfeff; color: #155e75; border-color: #a5f3fc; }
        .history-type-adjustment { background: #f5f3ff; color: #5b21b6; border-color: #ddd6fe; }
        .history-type-request { background: #fff7ed; color: #9a3412; border-color: #fed7aa; }
        .history-type-payout { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .history-type-default { background: #f3f4f6; color: #374151; border-color: #d1d5db; }

        .history-status-paid { background: #ecfdf3; color: #166534; border-color: #bbf7d0; }
        .history-status-pending { background: #fffbeb; color: #92400e; border-color: #fde68a; }
        .history-status-failed { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .history-status-progress { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .history-status-default { background: #f3f4f6; color: #374151; border-color: #d1d5db; }
    </style>

    <h1><?= htmlspecialchars((string) ($title ?? 'История'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="muted">Агент: <strong><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></strong></p>

    <div class="page-actions">
        <?php if ($isAgentMode): ?>
            <a class="btn" href="/cabinet">Назад в кабинет</a>
            <a class="btn" href="/my/payments">Оплачено</a>
            <a class="btn" href="/my/requests">Мои заявки</a>
        <?php else: ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Оплачено</a>
            <a class="btn" href="/requests?agent_user_id=<?= (int) $agentUserId ?>">Заявки</a>
        <?php endif; ?>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-bottom:20px;">
        <div class="card">
            <p class="muted">Операций</p>
            <p><strong><?= (int) ($summary['operations_count'] ?? 0) ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Баланс по истории</p>
            <p><strong><?= htmlspecialchars(number_format((float) ($summary['balance_total'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
    </div>

    <?php if (empty($history)): ?>
        <p class="muted">История операций пуста.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Тип операции</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Кто сделал</th>
                <th>Основание / комментарий</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($history as $row): ?>
                <?php
                $typeRaw = (string) ($row['type'] ?? '');
                $statusRaw = (string) ($row['status'] ?? '');

                $typeClass = match ($typeRaw) {
                    'Начисление' => 'history-type-accrual',
                    'Корректировка' => 'history-type-adjustment',
                    'Заявка исполнена' => 'history-type-request',
                    'Выплата', 'Выплата по заявке' => 'history-type-payout',
                    default => 'history-type-default',
                };

                $statusLabel = match ($statusRaw) {
                    'paid' => 'Оплачено',
                    'pending' => 'В ожидании',
                    'failed' => 'Неуспешно',
                    'in_progress', 'in_work' => 'В работе',
                    'completed', 'done' => 'Выполнено',
                    default => $statusRaw !== '' ? $statusRaw : '—',
                };

                $statusClass = match ($statusRaw) {
                    'paid', 'completed', 'done' => 'history-status-paid',
                    'pending' => 'history-status-pending',
                    'failed', 'rejected' => 'history-status-failed',
                    'in_progress', 'in_work' => 'history-status-progress',
                    default => 'history-status-default',
                };
                ?>
                <tr>
                    <td><?= htmlspecialchars(formatDateTime((string) ($row['date'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="history-badge <?= htmlspecialchars($typeClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($typeRaw !== '' ? $typeRaw : '—', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars(number_format((float) ($row['amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="history-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars((string) ($row['actor_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($row['comment'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection

