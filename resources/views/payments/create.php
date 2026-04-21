<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array $errors */ ?>
<?php /** @var array $old */ ?>
<?php $isAdminMode = $isAdminMode ?? false; ?>
<?php $agentUserId = $agentUserId ?? (int) $agent->id; ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Создать платеж</h1>
    <p class="muted">
        Агент:
        <strong><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></strong>
        (ID: <?= (int) $agent->id ?>)
    </p>

    <div class="page-actions">
        <?php if ($isAdminMode): ?>
            <a class="btn" href="/admin/agents">Назад к агентам</a>
            <a class="btn" href="/admin/agents/payments?agent_user_id=<?= (int) $agentUserId ?>">Назад к платежам</a>
        <?php else: ?>
            <a class="btn" href="/payments">Назад к платежам</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($errors['_form'])): ?>
        <p class="flash flash-error"><?= htmlspecialchars((string) $errors['_form'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST" action="/payments">
        <?= csrf_field() ?>

        <?php if ($isAdminMode): ?>
            <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="amount">Сумма</label>
            <input
                id="amount"
                type="text"
                name="amount"
                value="<?= htmlspecialchars((string) ($old['amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                required
            >
            <?php if (!empty($errors['amount'])): ?>
                <p class="field-error"><?= htmlspecialchars((string) $errors['amount'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="payment_date">Дата платежа</label>
            <input
                id="payment_date"
                type="date"
                name="payment_date"
                value="<?= htmlspecialchars((string) ($old['payment_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                required
            >
            <?php if (!empty($errors['payment_date'])): ?>
                <p class="field-error"><?= htmlspecialchars((string) $errors['payment_date'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="status">Статус</label>
            <select id="status" name="status" required>
                <?php $currentStatus = (string) ($old['status'] ?? 'pending'); ?>
                <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Ожидает</option>
                <option value="paid" <?= $currentStatus === 'paid' ? 'selected' : '' ?>>Оплачен</option>
                <option value="failed" <?= $currentStatus === 'failed' ? 'selected' : '' ?>>Ошибка</option>
            </select>
            <?php if (!empty($errors['status'])): ?>
                <p class="field-error"><?= htmlspecialchars((string) $errors['status'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="note">Примечание</label>
            <textarea id="note" name="note" rows="4"><?= htmlspecialchars((string) ($old['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (!empty($errors['note'])): ?>
                <p class="field-error"><?= htmlspecialchars((string) $errors['note'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
    </form>
</section>
@endsection


