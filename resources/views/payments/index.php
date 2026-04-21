<?php /** @var \Framework\Core\Collection $payments */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php $isAdminMode = $isAdminMode ?? false; ?>
<?php $agentUserId = $agentUserId ?? (int) $agent->id; ?>
<?php $isReadOnly = $isReadOnly ?? false; ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Платежи</h1>
    <p class="muted">
        Агент:
        <strong><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></strong>
        (ID: <?= (int) $agent->id ?>)
    </p>

    <div class="page-actions">
        <?php if ($isAdminMode): ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/requests?agent_user_id=<?= (int) $agentUserId ?>">Заявки</a>
            <a class="btn" href="/history?agent_user_id=<?= (int) $agentUserId ?>">Баланс / история</a>
            <?php if (!$isReadOnly): ?>
                <a class="btn btn-primary" href="/payments/create?agent_user_id=<?= (int) $agentUserId ?>">Создать платеж</a>
            <?php endif; ?>
        <?php else: ?>
            <a class="btn" href="/cabinet">Назад в кабинет</a>
            <a class="btn" href="/my/requests">Мои заявки</a>
            <a class="btn" href="/my/balance">Баланс / история</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($payments->count() === 0): ?>
        <p class="muted">Платежей пока нет.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Примечание</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= (int) $payment->id ?></td>
                    <td><?= htmlspecialchars((string) $payment->payment_date, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $payment->amount, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $payment->status, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($payment->note ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <div class="table-actions">
                            <?php if ($isAdminMode): ?>
                                <a class="btn" href="/payments/show?id=<?= (int) $payment->id ?>&agent_user_id=<?= (int) $agentUserId ?>">Просмотр</a>
                                <a class="btn" href="/payments/edit?id=<?= (int) $payment->id ?>&agent_user_id=<?= (int) $agentUserId ?>">Редактировать</a>
                                <form method="POST" action="/payments/delete" style="display:inline-block;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $payment->id ?>">
                                    <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить платеж?')">Удалить</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">Доступно только для просмотра</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


