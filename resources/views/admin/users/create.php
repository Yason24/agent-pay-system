<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
<?php /** @var array<string, string> $roles */ ?>
<?php /** @var array<string, string> $statuses */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Создать пользователя</h1>
    <p class="muted">Администратор создаёт пользователя и сразу назначает ему роль.</p>

    <div class="page-actions">
        <a class="btn" href="/admin/users">Назад к пользователям</a>
    </div>

    <form class="form-stack" method="post" action="/admin/users">
        <?= csrf_field() ?>

        <label class="form-label" for="user_last_name">Фамилия</label>
        <input class="form-input" id="user_last_name" type="text" name="last_name" value="<?= htmlspecialchars((string) ($old['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['last_name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['last_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_first_name">Имя</label>
        <input class="form-input" id="user_first_name" type="text" name="first_name" value="<?= htmlspecialchars((string) ($old['first_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['first_name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['first_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_middle_name">Отчество</label>
        <input class="form-input" id="user_middle_name" type="text" name="middle_name" value="<?= htmlspecialchars((string) ($old['middle_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['middle_name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['middle_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_login">Логин</label>
        <input class="form-input" id="user_login" type="text" name="login" value="<?= htmlspecialchars((string) ($old['login'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['login'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['login'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_phone">Телефон</label>
        <input class="form-input" id="user_phone" type="text" name="phone" value="<?= htmlspecialchars((string) ($old['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['phone'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['phone'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_city">Город</label>
        <input class="form-input" id="user_city" type="text" name="city" value="<?= htmlspecialchars((string) ($old['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <label class="form-label" for="user_email">Эл. почта</label>
        <input class="form-input" id="user_email" type="email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['email'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_password">Пароль</label>
        <input class="form-input" id="user_password" type="password" name="password" required>
        <?php if (!empty($errors['password'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_role">Роль</label>
        <?php $selectedRole = (string) ($old['role'] ?? 'agent'); ?>
        <select class="form-input" id="user_role" name="role" required>
            <?php foreach ($roles as $roleKey => $roleTitle): ?>
                <option value="<?= htmlspecialchars($roleKey, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedRole === $roleKey ? 'selected' : '' ?>><?= htmlspecialchars($roleTitle, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['role'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="user_status">Статус</label>
        <?php $selectedStatus = (string) ($old['status'] ?? 'active'); ?>
        <select class="form-input" id="user_status" name="status" required>
            <?php foreach ($statuses as $statusKey => $statusTitle): ?>
                <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedStatus === $statusKey ? 'selected' : '' ?>><?= htmlspecialchars($statusTitle, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['status'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Создать пользователя</button>
    </form>
</section>
@endsection


