<?php /** @var array<string, string> $errors */ ?>
<?php /** @var array<string, mixed> $old */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Create Agent</h1>

    <p><a href="/agents">Back to list</a></p>

    <form action="/agents" method="post">
        <label for="agent_name">Agent name</label>
        <input
            id="agent_name"
            type="text"
            name="name"
            value="<?= htmlspecialchars((string) ($old['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
            maxlength="255"
            required
        >

        <?php if (!empty($errors['name'])): ?>
            <p style="color:red; margin:0;"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button type="submit">Create</button>
    </form>
</section>
@endsection

