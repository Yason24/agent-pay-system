<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array<string, mixed> $paymentSummary */ ?>
<?php /** @var \Framework\Core\Collection $latestPayments */ ?>
@extends('layouts.app')

@section('content')
<section>
    <?php $agentDisplayName = \App\Models\User::composeFullName([
        'last_name' => (string) $agent->last_name,
        'first_name' => (string) $agent->first_name,
        'middle_name' => (string) $agent->middle_name,
        'name' => (string) $agent->name,
    ]); ?>
    <h1>Кабинет агента</h1>

    <?php if (!empty($_SESSION['agents_success'])): ?>
        <p class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['agents_success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['agents_success']); ?>
    <?php endif; ?>

    <div class="page-actions">
        <a class="btn" href="/dashboard">Назад в кабинет</a>
        <a class="btn" href="/history?agent_user_id=<?= (int) $agent->id ?>">Баланс</a>
        <a class="btn" href="/requests?agent_user_id=<?= (int) $agent->id ?>">Заявки</a>
        <a class="btn btn-primary" href="/payments?agent_user_id=<?= (int) $agent->id ?>">Оплачено</a>
    </div>

    <div class="card">
        <p><strong>ID:</strong> <?= (int) $agent->id ?></p>
        <p><strong>ФИО:</strong> <?= htmlspecialchars($agentDisplayName !== '' ? $agentDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-top:14px;">
        <div class="card"><p class="muted">Всего платежей</p><p><strong><?= (int) $paymentSummary['payments_count'] ?></strong></p></div>
        <div class="card"><p class="muted">Общая сумма</p><p><strong><?= htmlspecialchars(formatMoney($paymentSummary['total_amount']), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
        <div class="card"><p class="muted">Оплачено</p><p><strong><?= htmlspecialchars(formatMoney($paymentSummary['paid_amount']), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
        <div class="card"><p class="muted">В ожидании</p><p><strong><?= htmlspecialchars(formatMoney($paymentSummary['pending_amount']), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
        <div class="card"><p class="muted">Неуспешно</p><p><strong><?= htmlspecialchars(formatMoney($paymentSummary['failed_amount']), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
    </div>

    <div class="card" style="max-width:none; margin-top:14px;">
        <p><strong>Последние 5 платежей</strong></p>

        <?php if ($latestPayments->count() === 0): ?>
            <p class="muted" style="margin-bottom:0;">Пока нет платежей. Добавьте первый платеж, чтобы увидеть историю по агенту.</p>
        <?php else: ?>
            <table class="table" style="margin-top:0;">
                <thead>
                <tr>
                    <th>Сумма</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>Примечание</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latestPayments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars(formatMoney($payment->amount), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(formatDate((string) $payment->payment_date), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(payment_status_label((string) $payment->status), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $payment->note, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
@endsection

