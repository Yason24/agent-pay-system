<?php /** @var \Framework\Core\Collection $agents */ ?>
<?php /** @var string|null $success */ ?>
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>My Agents</h1>

    <p>
        <a href="/dashboard">Back to dashboard</a>
        |
        <a href="/agents/create">Create new agent</a>
    </p>

    <?php if (!empty($success)): ?>
        <p style="color:green;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($agents->count() === 0): ?>
        <p>No agents yet.</p>
    <?php else: ?>
        <table style="border-collapse: collapse; background: #fff; border:1px solid #d1d5db;">
            <thead>
                <tr>
                    <th style="padding:8px; border:1px solid #d1d5db;">ID</th>
                    <th style="padding:8px; border:1px solid #d1d5db;">Name</th>
                    <th style="padding:8px; border:1px solid #d1d5db;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agents as $agent): ?>
                    <tr>
                        <td style="padding:8px; border:1px solid #d1d5db;"><?= (int) $agent->id ?></td>
                        <td style="padding:8px; border:1px solid #d1d5db;"><?= htmlspecialchars((string) $agent->name, ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:8px; border:1px solid #d1d5db;">
                            <a href="/agents/show?id=<?= (int) $agent->id ?>">View</a>
                            |
                            <a href="/agents/edit?id=<?= (int) $agent->id ?>">Edit</a>
                            |
                            <form action="/agents/delete" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int) $agent->id ?>">
                                <button type="submit" onclick="return confirm('Delete this agent?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
@endsection


