<?php /** @var \Framework\Core\Collection $requests */ ?>
<?php /** @var \App\Models\User $agent */ ?>
<?php /** @var bool $isAgentMode */ ?>
<?php /** @var int $agentUserId */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1><?= htmlspecialchars((string) ($title ?? 'Заявки'), ENT_QUOTES, 'UTF-8') ?></h1>

    <?php if (!empty($agent)): ?>
        <p class="muted">Агент: <strong><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <?php endif; ?>

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

    <?php if (!empty($_SESSION['requests_success'])): ?>
        <p class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['requests_success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['requests_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['requests_error'])): ?>
        <p class="flash flash-error"><?= htmlspecialchars((string) $_SESSION['requests_error'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['requests_error']); ?>
    <?php endif; ?>

    <?php if ($requests->count() === 0): ?>
        <p class="muted">Заявок пока нет.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th>Ссылка на оплату</th>
                <th>Комментарий</th>
                <th>Действие</th>
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
                    <td><?= (int) $req->id ?></td>
                    <td><?= htmlspecialchars(formatDateTime((string) $req->created_at), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(number_format((float) $rowAmount, 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(\App\Models\Request::statusLabel((string) $req->status), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($rowLink)): ?>
                            <a href="<?= htmlspecialchars((string) $rowLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Открыть</a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string) ($req->comment ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php $status = (string) ($req->status ?? ''); ?>
                        <?php if (empty($isAgentMode) && $status === 'new'): ?>
                            <form method="POST" action="/requests/take" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $req->id ?>">
                                <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
                                <button type="submit" class="btn">Взять в работу</button>
                            </form>
                        <?php elseif (empty($isAgentMode) && ($status === 'in_progress' || $status === 'in_work')): ?>
                            <div style="margin-bottom:6px;">
                                В работе: <?= htmlspecialchars((string) ($req->taken_by_name ?: '—'), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <form method="POST" action="/requests/complete" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $req->id ?>">
                                <input type="hidden" name="agent_user_id" value="<?= (int) $agentUserId ?>">
                                <button type="submit" class="btn btn-primary">Исполнено</button>
                            </form>
                        <?php elseif ($status === 'in_progress' || $status === 'in_work'): ?>
                            В работе: <?= htmlspecialchars((string) ($req->taken_by_name ?: '—'), ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(formatDateTime((string) $req->updated_at), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection








