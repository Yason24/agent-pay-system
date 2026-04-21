<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Создание агента</h1>

    <div class="page-actions">
        <a class="btn" href="/agents">Назад к списку</a>
    </div>

    <form class="form-stack" action="/agents" method="post">
        <?= csrf_field() ?>

        <label class="form-label" for="agent_name">Имя агента</label>
        <input
            class="form-input"
            id="agent_name"
            type="text"
            name="name"
            value="<?= htmlspecialchars((string) ($old['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
            maxlength="255"
            required
        >

        <?php if (!empty($errors['name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Создать</button>
    </form>
</section>
@endsection

