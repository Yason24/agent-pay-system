<?php /** @var \App\Models\Agent $agent */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Редактирование агента</h1>

    <div class="page-actions">
        <a class="btn" href="/agents">Назад к списку</a>
        <a class="btn" href="/agents/show?id=<?= (int) $agent->id ?>">Карточка</a>
    </div>

    <form class="form-stack" action="/agents/update" method="post">
        <input type="hidden" name="id" value="<?= (int) $agent->id ?>">

        <label class="form-label" for="agent_name">Имя агента</label>
        <input
            class="form-input"
            id="agent_name"
            type="text"
            name="name"
            value="<?= htmlspecialchars((string) ($old['name'] ?? $agent->name), ENT_QUOTES, 'UTF-8') ?>"
            maxlength="255"
            required
        >

        <?php if (!empty($errors['name'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Сохранить</button>
    </form>
</section>
@endsection

