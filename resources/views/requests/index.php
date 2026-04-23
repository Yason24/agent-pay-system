<?php /** @var \Framework\Core\Collection $requests */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var bool $isAgentMode */ ?>
<?php /** @var int $agentUserId */ ?>
<?php $isAgentMode = $isAgentMode ?? ($is_agent ?? false); ?>
<?php $agentUserId = $agentUserId ?? (int) ($agent_user_id ?? 0); ?>
<?php $canManage = $canManage ?? ($can_manage ?? false); ?>
<?php
$agentDisplayName = trim((string) ($agentFullName ?? $agent_full_name ?? ''));
if ($agentDisplayName === '' && !empty($agent)) {
    $agentDisplayName = \App\Models\User::composeFullName([
        'last_name' => (string) $agent->last_name,
        'first_name' => (string) $agent->first_name,
        'middle_name' => (string) $agent->middle_name,
        'name' => (string) $agent->name,
    ]);
}
if ($agentDisplayName === '') {
    $agentDisplayName = '—';
}
$statusLabel = static function (string $status): string {
    return match ($status) {
        'new' => 'Новая',
        'in_progress' => 'В работе',
        'paid' => 'Оплачено',
        'rejected' => 'Отклонено',
        'cancelled' => 'Отменено',
        default => $status,
    };
};
?>
@extends('layouts.app')

@section('content')
<section>
    <h1><?= htmlspecialchars((string) ($title ?? $page_title ?? 'Заявки'), ENT_QUOTES, 'UTF-8') ?></h1>

    <p class="muted">Агент: <strong><?= htmlspecialchars($agentDisplayName, ENT_QUOTES, 'UTF-8') ?></strong></p>

    <div class="page-actions">
        <?php if (!empty($isAgentMode)): ?>
            <a class="btn" href="/cabinet">Назад в кабинет</a>
            <a class="btn btn-primary" href="/requests/create">Создать заявку</a>
            <a class="btn" href="/my/balance">Баланс</a>
            <a class="btn" href="/my/payments">Начисления</a>
        <?php else: ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Начисления</a>
            <a class="btn" href="/history?agent_user_id=<?= (int) $agentUserId ?>">Баланс</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['requests_success'])): ?>
        <p class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['requests_success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['requests_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['requests_error'])): ?>
        <p class="flash flash-error"><?= htmlspecialchars((string) $_SESSION['requests_error'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['requests_error']); ?>
    <?php endif; ?>

    <?php if ($requests->count() === 0): ?>
        <p class="muted">Заявки пока отсутствуют.</p>
        <?php if (!empty($isAgentMode)): ?>
            <p><a class="btn btn-primary" href="/requests/create">Создать заявку</a></p>
        <?php endif; ?>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>№</th>
                <th>Дата и время создания</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Ссылка на оплату</th>
                <th>Кто взял в работу</th>
                <th>Обновлено</th>
                <th>Изменить статус</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $req): ?>
                <?php
                $rowAmount = $req->requested_amount ?? $req->amount ?? null;
                $rowLink = $req->payment_link ?? $req->link ?? '';
                $statusRaw = strtolower(trim((string) ($req->status ?? '')));
                $statusNormalized = match ($statusRaw) {
                    'in_work' => 'in_progress',
                    'done' => 'paid',
                    default => $statusRaw,
                };
                $isFinal = in_array($statusNormalized, ['paid', 'rejected', 'cancelled'], true);
                $allowedTransitions = match ($statusNormalized) {
                    'new' => ['in_progress'],
                    'in_progress' => ['paid', 'rejected', 'cancelled'],
                    default => [],
                };
                $rowFormId = 'request-status-form-' . (int) $req->id;
                $rowSelectId = 'request-status-select-' . (int) $req->id;
                $takenByDisplayName = trim((string) ($req->taken_by_name ?? ''));
                ?>
                <tr>
                    <td><?= (int) $req->id ?></td>
                    <td><?= htmlspecialchars(formatDateTime((string) $req->created_at), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatMoney($rowAmount), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($statusLabel($statusNormalized), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($rowLink)): ?>
                            <a href="<?= htmlspecialchars((string) $rowLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Открыть</a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($takenByDisplayName !== '' ? $takenByDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatDateTime((string) $req->updated_at), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($canManage): ?>
                            <label for="<?= htmlspecialchars($rowSelectId, ENT_QUOTES, 'UTF-8') ?>" style="display:none;">Изменить статус заявки</label>
                            <select id="<?= htmlspecialchars($rowSelectId, ENT_QUOTES, 'UTF-8') ?>" class="form-input" name="status" form="<?= htmlspecialchars($rowFormId, ENT_QUOTES, 'UTF-8') ?>" <?= ($isFinal || $allowedTransitions === []) ? 'disabled' : '' ?>>
                                <?php if ($allowedTransitions !== []): ?>
                                    <?php foreach ($allowedTransitions as $nextStatus): ?>
                                        <option value="<?= htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabel($nextStatus), ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="<?= htmlspecialchars($statusNormalized, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabel($statusNormalized), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endif; ?>
                            </select>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($canManage): ?>
                            <form id="<?= htmlspecialchars($rowFormId, ENT_QUOTES, 'UTF-8') ?>" method="POST" action="/requests/change-status" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $req->id ?>">
                                <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
                                <?php if (!$isFinal && $allowedTransitions !== []): ?>
                                    <button type="submit" class="btn btn-primary">Изменить статус</button>
                                <?php else: ?>
                                    <button type="submit" class="btn" disabled>Изменить статус</button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection
