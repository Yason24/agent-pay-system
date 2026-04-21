<?php /** @var string $message */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>{{ $message }}</h1>
    <p class="muted">Публичная главная страница. Войдите в аккаунт, чтобы управлять агентами.</p>

    <div class="card">
        <p class="muted">Доступные действия:</p>
        <div class="page-actions">
            <a class="btn btn-primary" href="/login">Войти</a>
            <a class="btn" href="/register">Регистрация</a>
        </div>
    </div>
</section>
@endsection