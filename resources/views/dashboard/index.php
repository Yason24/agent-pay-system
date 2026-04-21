<?php /** @var \App\Models\User|null $user */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Кабинет</h1>
    <p class="muted">Добро пожаловать, {{ $user?->name ?? 'Пользователь' }}</p>
    <p class="muted">Вы находитесь в защищенной части приложения.</p>

    <div class="page-actions">
        <a class="btn btn-primary" href="/agents">Управлять агентами</a>
    </div>

    <form class="form-stack" method="POST" action="/logout">
        <button class="btn" type="submit">Выйти</button>
    </form>
</section>
@endsection