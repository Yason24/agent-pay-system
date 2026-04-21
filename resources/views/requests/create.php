<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var array<string, mixed> $old */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Создать заявку</h1>

    <div class="page-actions">
        <a class="btn" href="/cabinet">Назад в кабинет</a>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="form-stack" method="post" action="/requests/store">
        <?= csrf_field() ?>

        <label class="form-label" for="request_subject">Тема</label>
        <input class="form-input" id="request_subject" type="text" name="subject" value="<?= htmlspecialchars((string) ($old['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>

        <label class="form-label" for="request_comment">Комментарий</label>
        <textarea class="form-input" id="request_comment" name="comment" rows="5"><?= htmlspecialchars((string) ($old['comment'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

        <button class="btn btn-primary" type="submit">Отправить заявку</button>
    </form>
</section>
@endsection

