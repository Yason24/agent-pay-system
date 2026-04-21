<?php /** @var string $message */ ?>
<?php $this->extend('layouts.app'); ?>

<?php $this->startSection('content'); ?>
<section>
    <h1><?= htmlspecialchars($message) ?></h1>
    <p class="muted">Публичная главная страница. Войдите в аккаунт, чтобы управлять агентами.</p>

    <div class="card">
        <p class="muted">Доступные действия:</p>
        <div class="page-actions">
            <a class="btn btn-primary" href="/login">Войти</a>
        </div>
    </div>
</section>
<?php $this->endSection(); ?>