<?php /** @var \Framework\Core\Collection $agents */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var bool $canManageUsers */ ?>
<?php /** @var bool $canTopUp */ ?>
<?php /** @var bool $canViewProfile */ ?>
<?php /** @var string|null $search_query */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Агенты</h1>
    <p class="muted">Список пользователей с ролью «Агент».</p>

    <div class="page-actions">
        <a class="btn" href="/dashboard">Назад в кабинет</a>
        <?php if (!empty($canManageUsers)): ?>
            <a class="btn" href="/admin/users">Все пользователи</a>
            <a class="btn btn-primary" href="/admin/users/create">Создать пользователя</a>
        <?php endif; ?>
    </div>

    <form method="get" action="/agents" class="form-stack" style="max-width:520px; margin-bottom:16px;">
        <label class="form-label" for="agents_search">Поиск</label>
        <div class="actions-inline" style="width:100%;">
            <input
                class="form-input"
                id="agents_search"
                type="text"
                name="q"
                value="<?= htmlspecialchars((string) ($search_query ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Поиск по ФИО"
            >
            <button class="btn" type="submit">Найти</button>
        </div>
    </form>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($agents->count() === 0): ?>
        <p class="muted">Агенты не найдены.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ФИО</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($agents as $agent): ?>
                <?php
                $fullName = \App\Models\User::composeFullName([
                    'last_name' => (string) $agent->last_name,
                    'first_name' => (string) $agent->first_name,
                    'middle_name' => (string) $agent->middle_name,
                    'name' => (string) $agent->name,
                ]);
                ?>
                <tr>
                    <td><?= htmlspecialchars($fullName !== '' ? $fullName : '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <div class="actions-inline">
                            <a class="btn" href="/history?agent_user_id=<?= (int) $agent->id ?>">Баланс</a>
                            <a class="btn" href="/requests?agent_user_id=<?= (int) $agent->id ?>">Заявки</a>
                            <a class="btn" href="/payments?agent_user_id=<?= (int) $agent->id ?>">Начисления</a>
                            <?php if (!empty($canTopUp)): ?>
                                <a class="btn" href="/payments/create?agent_user_id=<?= (int) $agent->id ?>">Пополнить</a>
                            <?php endif; ?>
                            <?php if (!empty($canViewProfile)): ?>
                                <a class="btn" href="/agents/show?agent_user_id=<?= (int) $agent->id ?>">Просмотр</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


