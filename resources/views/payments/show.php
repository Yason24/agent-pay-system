<?php /** @var \App\Models\Payment $payment */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php $isAdminMode = $isAdminMode ?? false; ?>
<?php $agentUserId = $agentUserId ?? (int) $agent->id; ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Платеж #<?= (int) $payment->id ?></h1>

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
            <a class="btn" href="/admin/agents/payments?agent_user_id=<?= (int) $agentUserId ?>">Назад к платежам</a>
            <a class="btn" href="/payments/edit?id=<?= (int) $payment->id ?>&agent_user_id=<?= (int) $agentUserId ?>">Изменить</a>
        <?php else: ?>
            <a class="btn" href="/payments">Назад к платежам</a>
            <a class="btn" href="/payments/edit?id=<?= (int) $payment->id ?>">Изменить</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <p><strong>Агент:</strong> <?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Сумма:</strong> <?= htmlspecialchars(number_format((float) $payment->amount, 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Дата:</strong> <?= htmlspecialchars((string) $payment->payment_date, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Статус:</strong> <?= htmlspecialchars(payment_status_label((string) $payment->status), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Примечание:</strong> <?= htmlspecialchars(((string) $payment->note) !== '' ? (string) $payment->note : '-', ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>
@endsection


