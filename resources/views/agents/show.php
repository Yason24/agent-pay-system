<?php /** @var \App\Models\Agent $agent */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Карточка агента</h1>

    <?php if (!empty($_SESSION['agents_success'])): ?>
        <p class="flash flash-success"><?= htmlspecialchars((string) $_SESSION['agents_success'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['agents_success']); ?>
    <?php endif; ?>

    <div class="page-actions">
        <a class="btn" href="/agents">Назад к списку</a>
        <a class="btn" href="/payments?agent_id=<?= (int) $agent->id ?>">Платежи</a>
        <a class="btn" href="/agents/edit?id=<?= (int) $agent->id ?>">Изменить</a>
    </div>

    <div class="card">
        <p><strong>ID:</strong> <?= (int) $agent->id ?></p>
        <p><strong>Имя:</strong> <?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>
@endsection

