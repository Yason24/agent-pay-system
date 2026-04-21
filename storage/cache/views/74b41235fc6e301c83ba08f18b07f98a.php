<?php /** @var \App\Models\User|null $user */ ?>
<?php $this->extend('layouts.app'); ?>

<?php $this->startSection('content'); ?>
<section>
    <?php $isAdmin = $user !== null && (string) $user->role === 'admin'; ?>
    <h1>Кабинет</h1>
    <p class="muted">Добро пожаловать, <?= htmlspecialchars($user?->name ?? 'Пользователь') ?></p>
    <p class="muted">Вы находитесь в защищенной части приложения.</p>

    <div class="page-actions">
        <a class="btn btn-primary" href="/agents">Управлять агентами</a>
        <?php if ($isAdmin): ?>
            <a class="btn" href="/admin/users">Пользователи</a>
        <?php endif; ?>
    </div>

    <form class="form-stack" method="POST" action="/logout">
        <?= csrf_field() ?>
        <button class="btn" type="submit">Выйти</button>
    </form>
</section>
<?php $this->endSection(); ?>