<?php /** @var \Framework\Core\Collection $agents */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
	<h1>Агенты</h1>
	<p class="muted">Список пользователей с ролью «Агент».</p>

	<div class="page-actions">
		<a class="btn" href="/dashboard">Назад в кабинет</a>
		<a class="btn" href="/admin/users">Все пользователи</a>
		<a class="btn btn-primary" href="/admin/users/create">Создать пользователя</a>
	</div>

	<?php if (!empty($success)): ?>
		<p class="flash flash-success\"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
	<?php endif; ?>

	<?php if (!empty($error)): ?>
		<p class="flash flash-error\"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
	<?php endif; ?>

	<?php if ($agents->count() === 0): ?>
		<p class="muted">Агентов пока нет.</p>
	<?php else: ?>
		<table class="table">
			<thead>
			<tr>
				<th>ID</th>
				<th>Имя</th>
				<th>Эл. почта</th>
				<th>Роль</th>
				<th>Дата создания</th>
				<th>Действия</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($agents as $agent): ?>
				<tr>
					<td><?= (int) $agent->id ?></td>
					<td><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars((string) $agent->email, ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars(\App\Models\User::roleLabel((string) $agent->role), ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars((string) $agent->created_at, ENT_QUOTES, 'UTF-8') ?></td>
					<td>
						<div class="table-actions">
							<a class="btn" href="/admin/agents/payments?agent_user_id=<?= (int) $agent->id ?>">Платежи</a>
							<a class="btn" href="/admin/users/edit?id=<?= (int) $agent->id ?>">Просмотр</a>
							<a class="btn" href="/admin/users/edit?id=<?= (int) $agent->id ?>">Редактировать</a>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</section>
@endsection

