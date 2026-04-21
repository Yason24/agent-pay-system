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
        <?php if ($isAgentMode): ?>
            <a class="btn" href="/cabinet">Назад в кабинет</a>
            <a class="btn btn-primary" href="/requests/create">Создать заявку</a>
        <?php else: ?>
            <a class="btn" href="/agents">Назад к агентам</a>
            <a class="btn" href="/payments?agent_user_id=<?= (int) $agentUserId ?>">Платежи</a>
            <a class="btn" href="/history?agent_user_id=<?= (int) $agentUserId ?>">Баланс / история</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['requests_success'])): ?>
        <p class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['requests_success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['requests_success']); ?>
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
                <th>Взял в работу</th>
                <th>Обновлено</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= (int) $req->id ?></td>
                    <td><?= htmlspecialchars((string) $req->created_at, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(number_format((float) $req->requested_amount, 2, '.', ' '), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(\App\Models\Request::statusLabel((string) $req->status), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($req->payment_link)): ?>
                            <a href="<?= htmlspecialchars((string) $req->payment_link, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Открыть</a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string) ($req->comment ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($req->taken_by_name ?: '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $req->updated_at, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection

