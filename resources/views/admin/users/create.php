<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
<?php /** @var array<string, string> $roles */ ?>
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

        <label class="form-label" for="user_name">Имя</label>
        <input class="form-input" id="user_name" type="text" name="name" value="<?= htmlspecialchars((string) ($old['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (!empty($errors['name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

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

        <button class="btn btn-primary" type="submit">Создать пользователя</button>
    </form>
</section>
@endsection


