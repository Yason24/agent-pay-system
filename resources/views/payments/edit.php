<?php /** @var \App\Models\Payment $payment */ ?>
<?php /** @var \App\Models\Agent|null $agent */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Редактирование платежа</h1>
    <p class="muted">Агент: {{ $agent !== null ? $agent->name : ('#' . $payment->agent_id) }}</p>

    <div class="page-actions">
        <a class="btn" href="/payments?agent_id=<?= (int) $payment->agent_id ?>">Назад к платежам</a>
        <a class="btn" href="/payments/show?id=<?= (int) $payment->id ?>">Карточка платежа</a>
    </div>

    <?php $currentStatus = (string) ($old['status'] ?? $payment->status); ?>

    <form class="form-stack" action="/payments/update" method="post">
        <input type="hidden" name="id" value="<?= (int) $payment->id ?>">

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
            <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>pending</option>
            <option value="paid" <?= $currentStatus === 'paid' ? 'selected' : '' ?>>paid</option>
            <option value="failed" <?= $currentStatus === 'failed' ? 'selected' : '' ?>>failed</option>
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


