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

    <form method="POST" action="/login">
        <div>
            <label for="login_email">Email</label>
            <input id="login_email" type="email" name="email" required>
        </div>

        <div>
            <label for="login_password">Password</label>
            <input id="login_password" type="password" name="password" required>
        </div>

        <button type="submit">Sign in</button>
    </form>

    <p style="margin-top:12px;">
        No account? <a href="/register">Create one</a>
    </p>
</section>
@endsection