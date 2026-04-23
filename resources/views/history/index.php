<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array<string, mixed> $summary */ ?>
<?php /** @var \Framework\Core\Collection $payments */ ?>
<?php /** @var bool $isAgentMode */ ?>
<?php /** @var int $agentUserId */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1><?= htmlspecialchars((string) ($title ?? 'История'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="muted">Агент: <strong><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></strong></p>

    <div class="page-actions">
        <?php if ($isAgentMode): ?>
            <a class="btn" href="/cabinet">Назад в кабинет</a>
            <a class="btn" href="/my/payments">Мои начисления</a>
            <a class="btn" href="/my/requests">Мои заявки</a>
        <?php else: ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Начисления</a>
            <a class="btn" href="/requests?agent_user_id=<?= (int) $agentUserId ?>">Заявки</a>
        <?php endif; ?>
    </div>

    <!-- Summary cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; margin-bottom:20px;">
        <div class="card">
            <p class="muted">Всего платежей</p>
            <p><strong><?= (int) ($summary['payments_count'] ?? 0) ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Начислено (итого)</p>
            <p><strong><?= htmlspecialchars(number_format((float) ($summary['total_amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Оплачено</p>
            <p><strong><?= htmlspecialchars(number_format((float) ($summary['paid_amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">В ожидании</p>
            <p><strong><?= htmlspecialchars(number_format((float) ($summary['pending_amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
        <div class="card">
            <p class="muted">Неуспешно</p>
            <p><strong><?= htmlspecialchars(number_format((float) ($summary['failed_amount'] ?? 0), 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </div>
    </div>

    <!-- Transaction log -->
    <?php if ($payments->count() === 0): ?>
        <p class="muted">История платежей пуста.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Примечание</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= (int) $payment->id ?></td>
                    <td><?= htmlspecialchars(formatDate((string) $payment->payment_date), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(number_format((float) $payment->amount, 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(payment_status_label((string) $payment->status), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($payment->note ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection

