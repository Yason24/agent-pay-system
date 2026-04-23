<?php /** @var \App\Models\Payment $payment */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
<?php $isAdminMode = $isAdminMode ?? false; ?>
<?php $agentUserId = $agentUserId ?? (int) $agent->id; ?>
<?php $agentDisplayName = trim((string) ($agentFullName ?? '')); ?>
<?php if ($agentDisplayName === '') {
    $agentDisplayName = \App\Models\User::composeFullName([
        'last_name' => (string) $agent->last_name,
        'first_name' => (string) $agent->first_name,
        'middle_name' => (string) $agent->middle_name,
        'name' => (string) $agent->name,
    ]);
} ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Изменить платеж</h1>
    <p class="muted">Агент: <?= htmlspecialchars($agentDisplayName !== '' ? $agentDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></p>

    <div class="page-actions">
        <?php if ($isAdminMode): ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Оплачено</a>
        <?php else: ?>
            <a class="btn" href="/my/payments">Оплачено</a>
        <?php endif; ?>
    </div>

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
        <input class="form-input" id="payment_amount" type="text" name="amount" value="<?= htmlspecialchars((string) ($old['amount'] ?? $payment->amount), ENT_QUOTES, 'UTF-8') ?>" placeholder="Например: 10000,50 или 10 000" required>
        <?php if (!empty($errors['amount'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['amount'], ENT_QUOTES, 'UTF-8') ?></p>
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


