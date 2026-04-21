<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var \Framework\Core\Collection $payments */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Моя история</h1>

    <div class="page-actions">
        <a class="btn" href="/cabinet">Назад в кабинет</a>
        <a class="btn" href="/my/payments">Мои платежи</a>
    </div>

    <?php if ($payments->count() === 0): ?>
        <p class="muted">История пока пуста.</p>
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
                    <td><?= htmlspecialchars((string) $payment->payment_date, ENT_QUOTES, 'UTF-8') ?></td>
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

