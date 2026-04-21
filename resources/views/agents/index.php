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
            <pre>staffAgentsListMode: <?= !empty($staffAgentsListMode) ? 'true' : 'false' ?>
staffAgents count: <?= count($staffAgents) ?></pre>

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
                    <?php $legacyAgentIdDebug = $staffAgent['legacy_agent_id'] ?? null; ?>
                    <tr>
                        <td><?= (int) $staffAgent['user_id'] ?></td>
                        <td><?= htmlspecialchars((string) $staffAgent['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $staffAgent['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(\App\Models\User::roleLabel((string) $staffAgent['role']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="muted" style="margin-bottom:6px;">debug: user_id=<?= (int) $staffAgent['user_id'] ?>; legacy_agent_id=<?= $legacyAgentIdDebug !== null ? (int) $legacyAgentIdDebug : 'null' ?></div>

                            <?php if (!empty($staffAgent['legacy_agent_id'])): ?>
                                <div class="actions-inline">
                                    <a class="btn" href="/payments?agent_id=<?= (int) $staffAgent['legacy_agent_id'] ?>">Платежи</a>
                                    <a class="btn" href="/agents/show?id=<?= (int) $staffAgent['legacy_agent_id'] ?>">Открыть</a>
                                    <a class="btn" href="/agents/edit?id=<?= (int) $staffAgent['legacy_agent_id'] ?>">Изменить</a>
                                </div>
                            <?php else: ?>
                                <span class="muted">Нет legacy-записи</span>
                            <?php endif; ?>
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
                                <a class="btn" href="/payments?agent_id=<?= (int) $agent->id ?>">Платежи</a>
                                <a class="btn" href="/agents/show?id=<?= (int) $agent->id ?>">Открыть</a>
                                <a class="btn" href="/agents/edit?id=<?= (int) $agent->id ?>">Изменить</a>
                                <form action="/agents/delete" method="post" style="margin:0;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $agent->id ?>">
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Удалить этого агента?');">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


