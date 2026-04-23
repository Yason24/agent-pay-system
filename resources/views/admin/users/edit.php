<?php /** @var \App\Models\User $userRecord */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
<?php /** @var array<string, string> $roles */ ?>
<?php /** @var array<string, string> $statuses */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Редактировать пользователя</h1>

    <div class="card" style="max-width:none; margin-bottom:16px;">
        <p><strong>ID:</strong> <?= (int) $userRecord->id ?></p>
        <p><strong>Дата создания:</strong> <?= htmlspecialchars(formatDate((string) $userRecord->created_at), ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div class="page-actions">
        <a class="btn" href="/admin/users">Назад к пользователям</a>
    </div>

    <form class="form-stack" method="post" action="/admin/users/update">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $userRecord->id ?>">
        <?php $valueLastName = (string) ($old['last_name'] ?? (string) $userRecord->last_name); ?>
        <?php $valueFirstName = (string) ($old['first_name'] ?? (string) $userRecord->first_name); ?>
        <?php $valueMiddleName = (string) ($old['middle_name'] ?? (string) $userRecord->middle_name); ?>
        <?php $valueLogin = (string) ($old['login'] ?? (string) $userRecord->login); ?>
        <?php $valuePhone = (string) ($old['phone'] ?? (string) $userRecord->phone); ?>
        <?php $valueCity = (string) ($old['city'] ?? (string) $userRecord->city); ?>
        <?php $valueStatus = (string) ($old['status'] ?? (string) $userRecord->status); ?>

        <label class="form-label" for="user_last_name">Фамилия</label>
        <input class="form-input" id="user_last_name" type="text" name="last_name" value="<?= htmlspecialchars($valueLastName, ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['last_name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['last_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_first_name">Имя</label>
        <input class="form-input" id="user_first_name" type="text" name="first_name" value="<?= htmlspecialchars($valueFirstName, ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['first_name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['first_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_middle_name">Отчество</label>
        <input class="form-input" id="user_middle_name" type="text" name="middle_name" value="<?= htmlspecialchars($valueMiddleName, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['middle_name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['middle_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_login">Логин</label>
        <input class="form-input" id="user_login" type="text" name="login" value="<?= htmlspecialchars($valueLogin, ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['login'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['login'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_phone">Телефон</label>
        <input class="form-input" id="user_phone" type="text" name="phone" value="<?= htmlspecialchars($valuePhone, ENT_QUOTES, 'UTF-8') ?>">

        <label class="form-label" for="user_city">Город</label>
        <input class="form-input" id="user_city" type="text" name="city" value="<?= htmlspecialchars($valueCity, ENT_QUOTES, 'UTF-8') ?>">

        <label class="form-label" for="user_email">Эл. почта</label>
        <input class="form-input" id="user_email" type="email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? $userRecord->email), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['email'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_role">Роль</label>
        <?php $selectedRole = (string) ($old['role'] ?? $userRecord->role); ?>
        <select class="form-input" id="user_role" name="role" required>
            <?php foreach ($roles as $roleKey => $roleTitle): ?>
                <option value="<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedRole === $roleKey ? 'selected' : '' ?>><?= htmlspecialchars($roleTitle, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['role'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_status">Статус</label>
        <?php $selectedStatus = $valueStatus !== '' ? $valueStatus : 'active'; ?>
        <select class="form-input" id="user_status" name="status" required>
            <?php foreach ($statuses as $statusKey => $statusTitle): ?>
                <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedStatus === $statusKey ? 'selected' : '' ?>><?= htmlspecialchars($statusTitle, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['status'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Сохранить</button>
    </form>

    <form class="form-stack" method="post" action="/admin/users/reset-password" style="margin-top:16px;">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $userRecord->id ?>">

        <h3 style="margin: 0;">Изменить пароль</h3>
        <label class="form-label" for="reset_password">Новый пароль</label>
        <input class="form-input" id="reset_password" type="password" name="password" minlength="6" required>

        <button class="btn" type="submit">Изменить пароль</button>
    </form>
</section>
@endsection


