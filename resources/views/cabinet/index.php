<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array<string, mixed> $paymentSummary */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Кабинет агента</h1>
    <p class="muted">Добро пожаловать, <?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></p>

    <?php if (!empty($_SESSION['requests_success'])): ?>
        <p class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['requests_success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['requests_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['app_error'])): ?>
        <p class="flash flash-error"><?= htmlspecialchars((string) $_SESSION['app_error'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['app_error']); ?>
    <?php endif; ?>

    <div class="page-actions">
        <a class="btn btn-primary" href="/requests/create">Создать заявку</a>
        <a class="btn" href="/my/requests">Мои заявки</a>
        <a class="btn" href="/my/payments">Мои платежи</a>
        <a class="btn" href="/my/balance">Баланс / история</a>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-top:14px;">
        <div class="card">
            <p class="muted">Всего платежей</p>
            <p><strong><?= (int) $paymentSummary['payments_count'] ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Начислено (итого)</p>
            <p><strong><?= htmlspecialchars(number_format((float) $paymentSummary['total_amount'], 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Оплачено</p>
            <p><strong><?= htmlspecialchars(number_format((float) $paymentSummary['paid_amount'], 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">В ожидании</p>
            <p><strong><?= htmlspecialchars(number_format((float) $paymentSummary['pending_amount'], 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
    </div>
</section>
@endsection

