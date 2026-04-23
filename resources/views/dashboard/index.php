<?php /** @var \App\Models\User|null $user */ ?>
@extends('layouts.app')

@section('content')
<section>
    <?php $isAdmin = $user !== null && (string) $user->role === 'admin'; ?>
    <?php $welcomeName = (string) ($userFullName ?? 'Пользователь'); ?>
    <h1>Кабинет</h1>
    <p class="muted">Добро пожаловать, <?= htmlspecialchars($welcomeName, ENT_QUOTES, 'UTF-8') ?></p>
    <p class="muted">Вы находитесь в защищенной части приложения.</p>

    <div class="page-actions">
        <?php if ($user !== null && (string) $user->role === 'agent'): ?>
            <a class="btn btn-primary" href="/cabinet">Мой кабинет</a>
            <a class="btn" href="/my/payments">Начисления</a>
        <?php else: ?>
            <a class="btn btn-primary" href="/agents">Баланс</a>
            <?php if ($isAdmin): ?>
                <a class="btn" href="/admin/users">Пользователи</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
@endsection