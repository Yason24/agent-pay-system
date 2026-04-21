<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Register</h1>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

    <form class="form-stack" method="POST" action="/register">
        <label class="form-label" for="register_name">Name</label>
        <input class="form-input" id="register_name" type="text" name="name" placeholder="Your name">

        <label class="form-label" for="register_email">Email</label>
        <input class="form-input" id="register_email" type="email" name="email" required>

        <label class="form-label" for="register_password">Password</label>
        <input class="form-input" id="register_password" type="password" name="password" required>

        <label class="form-label" for="register_password_confirmation">Confirm password</label>
        <input class="form-input" id="register_password_confirmation" type="password" name="password_confirmation" required>

        <div class="page-actions" style="margin: 4px 0 0;">
            <button class="btn btn-primary" type="submit">Create account</button>
            <a class="btn" href="/login">Back to login</a>
        </div>
    </form>

    <p class="muted">
        Already have an account? <a href="/login">Sign in</a>
    </p>
</section>
@endsection

