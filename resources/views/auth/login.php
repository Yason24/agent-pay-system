<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Login</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php unset($_SESSION['auth_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/login">
        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Sign in</button>
    </form>
</section>
@endsection