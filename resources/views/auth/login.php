<?php /** @var string|null $error */ ?>
<?php /** @var string|null $success */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Login</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['auth_error']); ?>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color:green;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['auth_success']); ?>
    <?php endif; ?>

    <form action="/login" method="post">
        <label for="login_input">Логин: <input id="login_input" type="text" name="login" /></label>
        <br>
        <label for="password_input">Пароль: <input id="password_input" type="password" name="password" /></label>
        <br>
        <button type="submit">Ок</button>
        <button type="button" onclick="window.history.back();">Отмена</button>
        <br>
        <a href="/forgot-password">Забыли пароль?</a>
    </form>

    <p style="margin-top:12px;">
        No account? <a href="/register">Create one</a>
    </p>
</section>
@endsection