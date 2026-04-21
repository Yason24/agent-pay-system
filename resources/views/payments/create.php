<?php /** @var \App\Models\Agent $agent */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Создание платежа</h1>
    <p class="muted">Агент: {{ $agent->name }}</p>

    <div class="page-actions">
        <a class="btn" href="/payments?agent_id=<?= (int) $agent->id ?>">Назад к платежам</a>
    </div>

    <form class="form-stack" action="/payments" method="post">
        <input type="hidden" name="agent_id" value="<?= (int) $agent->id ?>">

        <?php if (!empty($errors['_form'])): ?>
            <p class="flash flash-error" style="margin-bottom: 0;"><?= htmlspecialchars($errors['_form'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_amount">Сумма</label>
        <input class="form-input" id="payment_amount" type="text" name="amount" value="<?= htmlspecialchars((string) ($old['amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['amount'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['amount'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_date">Дата платежа</label>
        <input class="form-input" id="payment_date" type="date" name="payment_date" value="<?= htmlspecialchars((string) ($old['payment_date'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['payment_date'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['payment_date'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_status">Статус</label>
        <?php $currentStatus = (string) ($old['status'] ?? 'pending'); ?>
        <select class="form-input" id="payment_status" name="status" required>
            <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>pending</option>
            <option value="paid" <?= $currentStatus === 'paid' ? 'selected' : '' ?>>paid</option>
            <option value="failed" <?= $currentStatus === 'failed' ? 'selected' : '' ?>>failed</option>
        </select>
        <?php if (!empty($errors['status'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="payment_note">Примечание</label>
        <textarea class="form-input" id="payment_note" name="note" rows="4" maxlength="1000"><?= htmlspecialchars((string) ($old['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        <?php if (!empty($errors['note'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['note'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Создать</button>
    </form>
</section>
@endsection


