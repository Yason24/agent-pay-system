<?php /** @var \App\Models\User|null $user */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Dashboard</h1>
    <p>Welcome, {{ $user?->name ?? 'Agent' }}</p>
    <p>You are now inside the protected application area.</p>

    <form method="POST" action="/logout">
        <button type="submit">Logout</button>
    </form>
</section>
@endsection