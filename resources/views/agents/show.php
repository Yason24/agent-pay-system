<?php /** @var \App\Models\Agent $agent */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Agent Card</h1>

    <p>
        <a href="/agents">Back to list</a>
        |
        <a href="/agents/edit?id=<?= (int) $agent->id ?>">Edit</a>
    </p>

    <div style="background:#fff; border:1px solid #d1d5db; padding:16px; max-width:520px;">
        <p><strong>ID:</strong> <?= (int) $agent->id ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>
@endsection

