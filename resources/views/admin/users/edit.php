<?php /** @var \App\Models\User $userRecord */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
<?php /** @var array<string, string> $roles */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Изменить роль пользователя</h1>

    <div class="card" style="max-width:none; margin-bottom:16px;">
        <p><strong>Имя:</strong> <?= htmlspecialchars((string) $userRecord->name, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Эл. почта:</strong> <?= htmlspecialchars((string) $userRecord->email, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Текущая роль:</strong> <?= htmlspecialchars(\App\Models\User::roleLabel((string) $userRecord->role), ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <div class="page-actions">
        <a class="btn" href="/admin/users">Назад к пользователям</a>
    </div>

    <form class="form-stack" method="post" action="/admin/users/update">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $userRecord->id ?>">

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

        <button class="btn btn-primary" type="submit">Обновить роль</button>
    </form>
</section>
@endsection


