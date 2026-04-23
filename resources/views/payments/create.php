<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var array $errors */ ?>
<?php /** @var array $old */ ?>
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
    <h1>Пополнить</h1>
    <p class="muted">
        Агент:
        <strong><?= htmlspecialchars($agentDisplayName !== '' ? $agentDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></strong>
    </p>

    <div class="page-actions">
        <?php if ($isAdminMode): ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Начисления</a>
        <?php else: ?>
            <a class="btn" href="/my/payments">Начисления</a>
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
                placeholder="Например: 10000,50 или 10 000"
                required
            >
            <?php if (!empty($errors['amount'])): ?>
                <p class="form-error"><?= htmlspecialchars((string) $errors['amount'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="note">Примечание</label>
            <textarea id="note" name="note" rows="4"><?= htmlspecialchars((string) ($old['note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (!empty($errors['note'])): ?>
                <p class="form-error"><?= htmlspecialchars((string) $errors['note'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
    </form>
</section>
@endsection


