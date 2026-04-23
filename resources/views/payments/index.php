<?php /** @var \Framework\Core\Collection $payments */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php $isAdminMode = $isAdminMode ?? false; ?>
<?php $agentUserId = $agentUserId ?? (int) $agent->id; ?>
<?php $isReadOnly = $isReadOnly ?? false; ?>
<?php $canDelete = $canDelete ?? false; ?>
<?php $canTopUp = $canTopUp ?? false; ?>
<?php $agentDisplayName = trim((string) ($agentFullName ?? '')); ?>
<?php if ($agentDisplayName === '') {
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
    <h1><?= htmlspecialchars((string) ($title ?? 'Начисления'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="muted">
        Агент:
        <strong><?= htmlspecialchars($agentDisplayName !== '' ? $agentDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></strong>
    </p>

    <div class="page-actions">
        <?php if ($isAdminMode): ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/requests?agent_user_id=<?= (int) $agentUserId ?>">Заявки</a>
            <a class="btn" href="/history?agent_user_id=<?= (int) $agentUserId ?>">Баланс</a>
            <?php if ($canTopUp && !$isReadOnly): ?>
                <a class="btn btn-primary" href="/payments/create?agent_user_id=<?= (int) $agentUserId ?>">Пополнить</a>
            <?php endif; ?>
        <?php else: ?>
            <a class="btn" href="/cabinet">Назад в кабинет</a>
            <a class="btn" href="/my/requests">Мои заявки</a>
            <a class="btn" href="/my/balance">Баланс</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($payments->count() === 0): ?>
        <p class="muted">Начислений пока нет.</p>
        <?php if ($isAdminMode && $canTopUp && !$isReadOnly): ?>
            <p><a class="btn btn-primary" href="/payments/create?agent_user_id=<?= (int) $agentUserId ?>">Пополнить</a></p>
        <?php endif; ?>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>№</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Примечание</th>
                <?php if ($isAdminMode && $canDelete): ?>
                    <th>Удалить</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $payment): ?>
                <?php
                $statusRaw = strtolower(trim((string) $payment->status));
                $canDeleteThis = !in_array($statusRaw, ['paid', 'оплачено'], true);
                $dateValue = (string) ($payment->created_at ?: $payment->payment_date);
                ?>
                <tr>
                    <td><?= (int) $payment->id ?></td>
                    <td><?= htmlspecialchars(formatDate($dateValue), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatMoney($payment->amount), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($payment->note ?? ''), ENT_QUOTES, 'UTF-8') ?></td>

                    <?php if ($isAdminMode && $canDelete): ?>
                        <td>
                            <?php if ($canDeleteThis): ?>
                                <form method="POST" action="/payments/delete" style="display:inline-block;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $payment->id ?>">
                                    <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить запись?')">Удалить</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


