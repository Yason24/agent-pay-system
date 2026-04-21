<?php /** @var \App\Models\Payment $payment */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
<?php $isAdminMode = $isAdminMode ?? false; ?>
<?php $agentUserId = $agentUserId ?? (int) $agent->id; ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Редактирование платежа</h1>
    <p class="muted">Агент: <?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></p>

    <div class="page-actions">
        <?php if ($isAdminMode): ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Назад к платежам</a>
            <a class="btn" href="/payments/show?id=<?= (int) $payment->id ?>&agent_user_id=<?= (int) $agentUserId ?>">Карточка платежа</a>
        <?php else: ?>
            <a class="btn" href="/my/payments">Назад к платежам</a>
        <?php endif; ?>
    </div>

    <?php $currentStatus = (string) ($old['status'] ?? $payment->status); ?>

    <form class="form-stack" action="/payments/update" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $payment->id ?>">
        <?php if ($isAdminMode): ?>
            <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
        <?php endif; ?>

        <?php if (!empty($errors['_form'])): ?>
            <p class="flash flash-error" style="margin-bottom: 0;"><?= htmlspecialchars($errors['_form'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_amount">Сумма</label>
        <input class="form-input" id="payment_amount" type="text" name="amount" value="<?= htmlspecialchars((string) ($old['amount'] ?? $payment->amount), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['amount'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['amount'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_date">Дата платежа</label>
        <input class="form-input" id="payment_date" type="date" name="payment_date" value="<?= htmlspecialchars((string) ($old['payment_date'] ?? $payment->payment_date), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['payment_date'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['payment_date'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_status">Статус</label>
        <select class="form-input" id="payment_status" name="status" required>
            <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>В ожидании</option>
            <option value="paid" <?= $currentStatus === 'paid' ? 'selected' : '' ?>>Оплачено</option>
            <option value="failed" <?= $currentStatus === 'failed' ? 'selected' : '' ?>>Неуспешно</option>
        </select>
        <?php if (!empty($errors['status'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_note">Примечание</label>
        <?php $noteValue = isset($old['note']) ? (string) $old['note'] : (string) $payment->note; ?>
        <textarea class="form-input" id="payment_note" name="note" rows="4" maxlength="1000"><?= htmlspecialchars($noteValue, ENT_QUOTES, 'UTF-8') ?></textarea>
        <?php if (!empty($errors['note'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['note'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Сохранить</button>
    </form>
</section>
@endsection


