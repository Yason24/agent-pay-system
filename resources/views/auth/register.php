<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Register</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/register">
        <div>
            <label for="register_name">Name</label>
            <input id="register_name" type="text" name="name" placeholder="Your name">
        </div>

        <div>
            <label for="register_email">Email</label>
            <input id="register_email" type="email" name="email" required>
        </div>

        <div>
            <label for="register_password">Password</label>
            <input id="register_password" type="password" name="password" required>
        </div>

        <div>
            <label for="register_password_confirmation">Confirm password</label>
            <input id="register_password_confirmation" type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Create account</button>
    </form>

    <p style="margin-top:12px;">
        Already have an account? <a href="/login">Sign in</a>
    </p>
</section>
@endsection

