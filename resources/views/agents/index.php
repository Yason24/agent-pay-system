<?php /** @var \Framework\Core\Collection $agents */ ?>
<?php /** @var array<int, array<string, mixed>> $staffAgents */ ?>
<?php /** @var bool $staffAgentsListMode */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1><?= !empty($staffAgentsListMode) ? 'Агенты (пользователи)' : 'Мои агенты' ?></h1>

    <div class="page-actions">
        <a class="btn" href="/dashboard">Назад в кабинет</a>
        <?php if (empty($staffAgentsListMode)): ?>
            <a class="btn btn-primary" href="/agents/create">Создать агента</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($staffAgentsListMode)): ?>
        <?php if (empty($staffAgents)): ?>
            <p class="muted">Пользователи с ролью "Агент" не найдены.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($staffAgents as $staffAgent): ?>
                    <?php $agentUserId = (int) ($staffAgent['user_id'] ?? 0); ?>
                    <tr>
                        <td><?= $agentUserId ?></td>
                        <td><?= htmlspecialchars((string) $staffAgent['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $staffAgent['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(\App\Models\User::roleLabel((string) $staffAgent['role']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="actions-inline">
                                <a class="btn" href="/admin/agents/payments?agent_user_id=<?= $agentUserId ?>">Платежи</a>
                                <a class="btn" href="/admin/agents/show?agent_user_id=<?= $agentUserId ?>">Просмотр</a>
                                <a class="btn" href="/admin/users/edit?id=<?= $agentUserId ?>">Редактировать</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php elseif ($agents->count() === 0): ?>
        <p class="muted">Пока нет агентов.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agents as $agent): ?>
                    <tr>
                        <td><?= (int) $agent->id ?></td>
                        <td><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="actions-inline">
                                <a class="btn" href="/payments">Платежи</a>
                                <a class="btn" href="/agents">Открыть кабинет</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


