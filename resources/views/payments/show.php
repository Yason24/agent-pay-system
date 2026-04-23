<?php /** @var \App\Models\Payment $payment */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php $isAdminMode = $isAdminMode ?? false; ?>
<?php $agentUserId = $agentUserId ?? (int) $agent->id; ?>
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
    <h1>Оплачено</h1>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['payments_success']); ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['payments_error']); ?>
    <?php endif; ?>

    <div class="page-actions">
        <?php if ($isAdminMode): ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Оплачено</a>
        <?php else: ?>
            <a class="btn" href="/my/payments">Оплачено</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <p><strong>Агент:</strong> <?= htmlspecialchars($agentDisplayName !== '' ? $agentDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Сумма:</strong> <?= htmlspecialchars(formatMoney($payment->amount), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Дата:</strong> <?= htmlspecialchars(formatDate((string) $payment->payment_date), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Статус:</strong> <?= htmlspecialchars(payment_status_label((string) $payment->status), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Примечание:</strong> <?= htmlspecialchars(((string) $payment->note) !== '' ? (string) $payment->note : '-', ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>
@endsection


