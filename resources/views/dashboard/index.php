<?php /** @var \App\Models\User|null $user */ ?>
@extends('layouts.app')

@section('content')
<section>
    <?php $isAdmin = $user !== null && (string) $user->role === 'admin'; ?>
    <h1>Кабинет</h1>
    <p class="muted">Добро пожаловать, {{ $user?->name ?? 'Пользователь' }}</p>
    <p class="muted">Вы находитесь в защищенной части приложения.</p>

    <div class="page-actions">
        <?php if ($user !== null && (string) $user->role === 'agent'): ?>
            <a class="btn btn-primary" href="/cabinet">Мой кабинет</a>
            <a class="btn" href="/my/payments">Мои платежи</a>
        <?php else: ?>
            <a class="btn btn-primary" href="/agents">Агенты</a>
            <?php if ($isAdmin): ?>
                <a class="btn" href="/admin/users">Пользователи</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <form class="form-stack" method="POST" action="/logout">
        <?= csrf_field() ?>
        <button class="btn" type="submit">Выйти</button>
    </form>
</section>
@endsection