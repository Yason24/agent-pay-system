<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Создать заявку</h1>

    <div class="page-actions">
        <a class="btn" href="/cabinet">Назад в кабинет</a>
        <a class="btn" href="/my/requests">Мои заявки</a>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="form-stack" method="post" action="/requests/store">
        <?= csrf_field() ?>

        <label class="form-label" for="req_amount">Сумма <span style="color:var(--danger)">*</span></label>
        <input class="form-input" id="req_amount" type="text" name="amount"
               value="<?= htmlspecialchars((string) ($old['amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Например: 10000,50 или 10 000" required>
        <?php if (!empty($errors['amount'])): ?>
            <p class="form-error"><?= htmlspecialchars((string) $errors['amount'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="req_payment_link">Ссылка на оплату</label>
        <input class="form-input" id="req_payment_link" type="url" name="payment_link"
               value="<?= htmlspecialchars((string) ($old['payment_link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
               placeholder="https://...">
        <?php if (!empty($errors['payment_link'])): ?>
            <p class="form-error"><?= htmlspecialchars((string) $errors['payment_link'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label class="form-label" for="req_comment">Комментарий</label>
        <textarea class="form-input" id="req_comment" name="comment" rows="4"><?= htmlspecialchars((string) ($old['comment'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

        <button class="btn btn-primary" type="submit">Отправить заявку</button>
    </form>
</section>
@endsection

