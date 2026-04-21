<?php /** @var \App\Models\Agent $agent */ ?>
<?php /** @var \Framework\Core\Collection $payments */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Платежи: {{ $agent->name }}</h1>

    <div class="page-actions">
        <a class="btn" href="/agents">К агентам</a>
        <a class="btn" href="/agents/show?id=<?= (int) $agent->id ?>">Карточка агента</a>
        <a class="btn btn-primary" href="/payments/create?agent_id=<?= (int) $agent->id ?>">Создать платеж</a>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($payments->count() === 0): ?>
        <p class="muted">Пока нет платежей.</p>
    <?php else: ?>
        <table class="table">
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
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= (int) $payment->id ?></td>
                        <td><?= htmlspecialchars(number_format((float) $payment->amount, 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $payment->payment_date, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $payment->status, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($payment->note ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="actions-inline">
                                <a class="btn" href="/payments/show?id=<?= (int) $payment->id ?>">Открыть</a>
                                <a class="btn" href="/payments/edit?id=<?= (int) $payment->id ?>">Изменить</a>
                                <form action="/payments/delete" method="post" style="margin:0;">
                                    <input type="hidden" name="id" value="<?= (int) $payment->id ?>">
                                    <button class="btn btn-danger" type="submit" onclick="return confirm('Удалить этот платеж?');">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


