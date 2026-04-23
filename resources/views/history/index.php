<?php /** @var array<string, mixed> $summary */ ?>
<?php /** @var array<int, array<string, mixed>> $history */ ?>
<?php $isAgentMode = $isAgentMode ?? ($is_agent ?? false); ?>
<?php $agentUserId = $agentUserId ?? (int) ($agent_user_id ?? 0); ?>
<?php $canTopUp = $canTopUp ?? false; ?>
<?php $agentDisplayName = trim((string) ($agent_full_name ?? '')); ?>
<?php if ($agentDisplayName === '' && !empty($agent) && $agent instanceof \App\Models\User) {
    $agentDisplayName = \App\Models\User::composeFullName([
        'last_name' => (string) $agent->last_name,
        'first_name' => (string) $agent->first_name,
        'middle_name' => (string) $agent->middle_name,
        'name' => (string) $agent->name,
    ]);
} ?>
@extends('layouts.app')

@section('content')
<section>
    <h1><?= htmlspecialchars((string) ($title ?? 'Баланс'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="muted">Агент: <strong><?= htmlspecialchars($agentDisplayName !== '' ? $agentDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></strong></p>

    <div class="page-actions">
        <?php if ($isAgentMode): ?>
            <a class="btn" href="/my/requests">Мои заявки</a>
            <a class="btn" href="/my/payments">Начисления</a>
        <?php else: ?>
            <a class="btn" href="/requests?agent_user_id=<?= (int) $agentUserId ?>">Заявки</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Начисления</a>
        <?php endif; ?>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-bottom:20px;">
        <div class="card">
            <p class="muted">Начислено</p>
            <p><strong><?= htmlspecialchars(formatMoney($summary['accrued'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Оплачено</p>
            <p><strong><?= htmlspecialchars(formatMoney($summary['paid_out'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Остаток</p>
            <p><strong><?= htmlspecialchars(formatMoney($summary['net_total'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
    </div>

    <?php if (empty($history)): ?>
        <p class="muted">История баланса пуста.</p>
        <?php if ($isAgentMode): ?>
            <p><a class="btn btn-primary" href="/requests/create">Создать заявку</a></p>
        <?php elseif ($agentUserId > 0 && $canTopUp): ?>
            <p><a class="btn" href="/payments/create?agent_user_id=<?= (int) $agentUserId ?>">Пополнить</a></p>
        <?php endif; ?>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>№</th>
                <th>Дата</th>
                <th>Тип</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Кто сделал</th>
                <th>Примечание</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($history as $row): ?>
                <?php
                $sourceId = (int) ($row['source_id'] ?? 0);
                $sourceType = strtolower(trim((string) ($row['source'] ?? '')));
                ?>
                <tr>
                    <td>
                        <?php if ($sourceType === 'payment'): ?>
                            Н-<?= $sourceId ?>
                        <?php elseif ($sourceType === 'request'): ?>
                            О-<?= $sourceId ?>
                        <?php else: ?>
                            <?= $sourceId ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(formatDateTime((string) ($row['date'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($row['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatMoney($row['amount'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($row['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($row['actor_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($row['comment'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection

