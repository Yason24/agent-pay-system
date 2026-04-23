<?php /** @var \Framework\Core\Collection $users */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var string|null $search_query */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Пользователи</h1>
    <p class="muted">Управление пользователями доступно только администратору.</p>

    <div class="page-actions">
        <a class="btn" href="/dashboard">Назад в кабинет</a>
        <a class="btn" href="/agents">Оплата</a>
        <a class="btn btn-primary" href="/admin/users/create">Создать пользователя</a>
    </div>

    <form method="get" action="/admin/users" class="form-stack" style="max-width:520px; margin-bottom:16px;">
        <label class="form-label" for="users_search">Поиск</label>
        <div class="actions-inline" style="width:100%;">
            <input
                class="form-input"
                id="users_search"
                type="text"
                name="q"
                value="<?= htmlspecialchars((string) ($search_query ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Поиск по ФИО или логину"
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

    <?php if ($users->count() === 0): ?>
        <p class="muted">Пользователи не найдены.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>№</th>
                <th>ФИО</th>
                <th>Логин</th>
                <th>Телефон</th>
                <th>Эл. почта</th>
                <th>Город</th>
                <th>Роль</th>
                <th>Статус</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <?php
                $fullName = \App\Models\User::composeFullName([
                    'last_name' => (string) $user->last_name,
                    'first_name' => (string) $user->first_name,
                    'middle_name' => (string) $user->middle_name,
                    'name' => (string) $user->name,
                ]);
                ?>
                <tr>
                    <td><?= (int) $user->id ?></td>
                    <td><?= htmlspecialchars($fullName !== '' ? $fullName : '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($user->login ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($user->phone ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $user->email, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($user->city ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(\App\Models\User::roleLabel((string) $user->role), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(\App\Models\User::statusLabel((string) ($user->status ?? 'active')), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatDate((string) $user->created_at), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <div class="actions-inline">
                            <a class="btn" href="/admin/users/edit?id=<?= (int) $user->id ?>">Изменить</a>
                            <a class="btn" href="/admin/users/edit?id=<?= (int) $user->id ?>#change-password">Изменить пароль</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


