<?php /** @var \Framework\Core\Collection $agents */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Мои агенты</h1>

    <div class="page-actions">
        <a class="btn" href="/dashboard">Назад в кабинет</a>
        <a class="btn btn-primary" href="/agents/create">Создать агента</a>
    </div>

    <?php if (!empty($success)): ?>
        <p class="flash flash-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($agents->count() === 0): ?>
        <p class="muted">Пока нет агентов.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agents as $agent): ?>
                    <tr>
                        <td><?= (int) $agent->id ?></td>
                        <td><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="actions-inline">
                                <a class="btn" href="/payments?agent_id=<?= (int) $agent->id ?>">Платежи</a>
                                <a class="btn" href="/agents/show?id=<?= (int) $agent->id ?>">Открыть</a>
                                <a class="btn" href="/agents/edit?id=<?= (int) $agent->id ?>">Изменить</a>
                                <form action="/agents/delete" method="post" style="margin:0;">
                                <input type="hidden" name="id" value="<?= (int) $agent->id ?>">
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Удалить этого агента?');">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


