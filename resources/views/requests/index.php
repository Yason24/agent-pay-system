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
            <a class="btn" href="/my/payments">Оплачено</a>
        <?php else: ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Оплачено</a>
            <a class="btn" href="/history?agent_user_id=<?= (int) $agentUserId ?>">Баланс</a>
        <?php endif; ?>
    </div>

    <?php if ($canManage): ?>
        <?php
        $newRequests = [];
        $inProgressRequests = [];

        foreach ($requests as $row) {
            $statusRaw = strtolower(trim((string) $row->status));
            $statusNormalized = match ($statusRaw) {
                'in_work' => 'in_progress',
                'done' => 'paid',
                default => $statusRaw,
            };

            if ($statusNormalized === 'new') {
                $newRequests[] = $row;
            }

            if ($statusNormalized === 'in_progress') {
                $inProgressRequests[] = $row;
            }
        }
        ?>

        <?php if ($newRequests !== [] || $inProgressRequests !== []): ?>
            <div class="card" style="max-width:none; margin-bottom:12px;">
                <p><strong>Управление</strong></p>

                <?php if ($newRequests !== []): ?>
                    <form method="POST" action="/requests/take" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:8px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
                        <label for="take_request_id">Взять в работу:</label>
                        <select id="take_request_id" name="request_id" class="form-input" style="max-width:220px;" required>
                            <?php foreach ($newRequests as $item): ?>
                                <option value="<?= (int) $item->id ?>">#<?= (int) $item->id ?>, <?= htmlspecialchars(formatMoney($item->requested_amount ?? $item->amount ?? 0), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn">Взять</button>
                    </form>
                <?php endif; ?>

                <?php if ($inProgressRequests !== []): ?>
                    <form method="POST" action="/requests/change-status" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
                        <label for="change_request_id">Изменить статус:</label>
                        <select id="change_request_id" name="request_id" class="form-input" style="max-width:220px;" required>
                            <?php foreach ($inProgressRequests as $item): ?>
                                <option value="<?= (int) $item->id ?>">#<?= (int) $item->id ?>, <?= htmlspecialchars(formatMoney($item->requested_amount ?? $item->amount ?? 0), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="change_request_status">Новый статус:</label>
                        <select id="change_request_status" name="status" class="form-input" style="max-width:180px;" required>
                            <option value="paid">Оплачено</option>
                            <option value="rejected">Отклонено</option>
                            <option value="cancelled">Отменено</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

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
                <th>Дата и время создания</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Ссылка на оплату</th>
                <th>Кто взял в работу</th>
                <th>Обновлено</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $req): ?>
                <?php
                $rowAmount = $req->requested_amount ?? $req->amount ?? null;
                $rowLink = $req->payment_link ?? $req->link ?? '';
                ?>
                <tr>
                    <td><?= htmlspecialchars(formatDateTime((string) $req->created_at), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatMoney($rowAmount), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php
                    $statusRaw = strtolower(trim((string) ($req->status ?? '')));
                    $statusNormalized = match ($statusRaw) {
                        'in_work' => 'in_progress',
                        'done' => 'paid',
                        default => $statusRaw,
                    };
                    $statusLabel = match ($statusNormalized) {
                        'new' => 'Новая',
                        'in_progress' => 'В работе',
                        'paid' => 'Оплачено',
                        'rejected' => 'Отклонено',
                        'cancelled' => 'Отменено',
                        default => $statusNormalized,
                    };
                    ?>
                    <td><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($rowLink)): ?>
                            <a href="<?= htmlspecialchars((string) $rowLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Открыть</a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <?php $takenByDisplayName = trim((string) ($req->taken_by_name ?? '')); ?>
                    <td><?= htmlspecialchars($takenByDisplayName !== '' ? $takenByDisplayName : '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatDateTime((string) $req->updated_at), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection








