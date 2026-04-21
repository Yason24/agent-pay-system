<?php /** @var \App\Models\Payment $payment */ ?>
<?php /** @var \App\Models\Agent|null $agent */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Платеж #<?= (int) $payment->id ?></h1>

    <?php $agentName = $agent !== null ? (string) $agent->name : ('#' . (int) $payment->agent_id); ?>
    <?php $paymentNote = (string) $payment->note; ?>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['payments_success']); ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['payments_error']); ?>
    <?php endif; ?>

    <div class="page-actions">
        <a class="btn" href="/payments?agent_id=<?= (int) $payment->agent_id ?>">Назад к платежам</a>
        <a class="btn" href="/agents/show?id=<?= (int) $payment->agent_id ?>">Карточка агента</a>
        <a class="btn" href="/payments/edit?id=<?= (int) $payment->id ?>">Изменить</a>
    </div>

    <div class="card">
        <p><strong>Агент:</strong> <?= htmlspecialchars($agentName, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Сумма:</strong> <?= htmlspecialchars(number_format((float) $payment->amount, 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Дата:</strong> <?= htmlspecialchars((string) $payment->payment_date, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Статус:</strong> <?= htmlspecialchars(payment_status_label((string) $payment->status), ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Примечание:</strong> <?= htmlspecialchars($paymentNote !== '' ? $paymentNote : '-', ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>
@endsection


