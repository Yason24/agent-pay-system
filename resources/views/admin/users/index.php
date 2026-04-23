<?php /** @var \Framework\Core\Collection $users */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Пользователи</h1>
    <p class="muted">Управление пользователями доступно только администратору.</p>

    <div class="page-actions">
        <a class="btn" href="/dashboard">Назад в кабинет</a>
        <a class="btn" href="/agents">Агенты</a>
        <a class="btn btn-primary" href="/admin/users/create">Создать пользователя</a>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($users->count() === 0): ?>
        <p class="muted">Пользователей пока нет.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>ФИО</th>
                <th>Логин</th>
                <th>Телефон</th>
                <th>Эл. почта</th>
                <th>Город</th>
                <th>Статус</th>
                <th>Роль</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars(
                        \App\Models\User::composeFullName([
                            'last_name' => (string) ($user->last_name ?? ''),
                            'first_name' => (string) ($user->first_name ?? ''),
                            'middle_name' => (string) ($user->middle_name ?? ''),
                            'name' => (string) $user->name,
                        ]),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></td>
                    <td><?= htmlspecialchars((string) ($user->login ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($user->phone ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $user->email, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($user->city ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(\App\Models\User::statusLabel((string) ($user->status ?? 'active')), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(\App\Models\User::roleLabel((string) $user->role), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(formatDate((string) $user->created_at), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <div class="actions-inline">
                            <a class="btn" href="/admin/users/edit?id=<?= (int) $user->id ?>">Редактировать</a>
                            <form method="post" action="/admin/users/reset-password">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $user->id ?>">
                                <input type="hidden" name="password" value="123456">
                                <button class="btn" type="submit" onclick="return confirm('Изменить пароль пользователя на временный?')">Изменить пароль</button>
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


