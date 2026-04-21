<?php /** @var string|null $error */ ?>
<?php /** @var string|null $success */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Login</h1>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['auth_error']); ?>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['auth_success']); ?>
    <?php endif; ?>

    <form class="form-stack" action="/login" method="post">
        <label class="form-label" for="login_input">Логин (имя или email)</label>
        <input class="form-input" id="login_input" type="text" name="login" required>

        <label class="form-label" for="password_input">Пароль</label>
        <input class="form-input" id="password_input" type="password" name="password" required>

        <div class="page-actions" style="margin: 4px 0 0;">
            <button class="btn btn-primary" type="submit">Войти</button>
            <a class="btn" href="/">Отмена</a>
        </div>

        <p><a href="/forgot-password">Забыли пароль?</a></p>
    </form>

    <p class="muted">
        No account? <a href="/register">Create one</a>
    </p>
</section>
@endsection