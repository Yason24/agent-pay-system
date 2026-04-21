<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array<string, mixed> $paymentSummary */ ?>
<?php /** @var \Framework\Core\Collection $latestPayments */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Кабинет агента</h1>

    <?php if (!empty($_SESSION['agents_success'])): ?>
        <p class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['agents_success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['agents_success']); ?>
    <?php endif; ?>

    <div class="page-actions">
        <a class="btn" href="/dashboard">Назад в кабинет</a>
        <a class="btn btn-primary" href="/my/payments">Мои платежи</a>
        <a class="btn" href="/my/history">Моя история</a>
        <a class="btn" href="/requests/create">Создать заявку</a>
    </div>

    <div class="card">
        <p><strong>ID:</strong> <?= (int) $agent->id ?></p>
        <p><strong>Имя:</strong> <?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-top:14px;">
        <div class="card"><p class="muted">Всего платежей</p><p><strong><?= (int) $paymentSummary['payments_count'] ?></strong></p></div>
        <div class="card"><p class="muted">Общая сумма</p><p><strong><?= htmlspecialchars(number_format((float) $paymentSummary['total_amount'], 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
        <div class="card"><p class="muted">Оплачено</p><p><strong><?= htmlspecialchars(number_format((float) $paymentSummary['paid_amount'], 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
        <div class="card"><p class="muted">В ожидании</p><p><strong><?= htmlspecialchars(number_format((float) $paymentSummary['pending_amount'], 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
        <div class="card"><p class="muted">Неуспешно</p><p><strong><?= htmlspecialchars(number_format((float) $paymentSummary['failed_amount'], 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p></div>
    </div>

    <div class="card" style="max-width:none; margin-top:14px;">
        <p><strong>Последние 5 платежей</strong></p>

        <?php if ($latestPayments->count() === 0): ?>
            <p class="muted" style="margin-bottom:0;">Пока нет платежей. Добавьте первый платеж, чтобы увидеть историю по агенту.</p>
        <?php else: ?>
            <table class="table" style="margin-top:0;">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Сумма</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>Примечание</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latestPayments as $payment): ?>
                    <tr>
                        <td><?= (int) $payment->id ?></td>
                        <td><?= htmlspecialchars(number_format((float) $payment->amount, 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $payment->payment_date, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(payment_status_label((string) $payment->status), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $payment->note, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="muted">Только просмотр</span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
@endsection

