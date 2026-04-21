<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Создание пользователя</h1>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

    <form class="form-stack" method="POST" action="/register">
        <?= csrf_field() ?>

        <label class="form-label" for="register_name">Имя</label>
        <input class="form-input" id="register_name" type="text" name="name" placeholder="Имя пользователя">

        <label class="form-label" for="register_email">Эл. почта</label>
        <input class="form-input" id="register_email" type="email" name="email" required>

        <label class="form-label" for="register_password">Пароль</label>
        <input class="form-input" id="register_password" type="password" name="password" required>

        <label class="form-label" for="register_password_confirmation">Подтверждение пароля</label>
        <input class="form-input" id="register_password_confirmation" type="password" name="password_confirmation" required>

        <div class="page-actions" style="margin: 4px 0 0;">
            <button class="btn btn-primary" type="submit">Создать пользователя</button>
            <a class="btn" href="/login">Назад ко входу</a>
        </div>
    </form>

    <p class="muted">
        Публичная регистрация отключена. Пользователя создаёт администратор.
    </p>
</section>
@endsection

