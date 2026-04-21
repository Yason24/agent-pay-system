<?php /** @var string $content */ ?>
<?php
$currentUser = app(\App\Services\AuthService::class)->user();
$currentRole = $currentUser !== null ? (string) $currentUser->role : 'guest';
$roleLabel = $currentRole === 'guest'
    ? 'Гость'
    : \App\Models\User::roleLabel($currentRole);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) ($title ?? 'Agent Pay System'), ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root {
            --bg: #f3f6fb;
            --surface: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #d1d5db;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --danger: #dc2626;
            --danger-hover: #b91c1c;
            --success-bg: #ecfdf3;
            --success-text: #166534;
            --error-bg: #fef2f2;
            --error-text: #991b1b;
        }

        * { box-sizing: border-box; }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            background: var(--bg);
            color: var(--text);
            line-height: 1.45;
        }

        header, footer {
            background: #111827;
            color: #fff;
            padding: 16px 24px;
        }

        .header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 10px;
            border: 1px solid #374151;
            border-radius: 999px;
            background: #1f2937;
            color: #e5e7eb;
            font-size: 13px;
            font-weight: 600;
        }

        main {
            max-width: 980px;
            margin: 0 auto;
            padding: 28px 20px 40px;
        }

        section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
        }

        h1 { margin: 0 0 8px; font-size: 28px; }
        p { margin: 0 0 12px; }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 14px 0 16px;
        }

        .form-stack {
            display: grid;
            gap: 10px;
            max-width: 420px;
            margin-top: 8px;
        }

        .form-label { font-weight: 600; }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
        }

        textarea.form-input,
        select.form-input {
            font-family: inherit;
        }

        .form-error {
            margin: 0;
            color: var(--error-text);
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            padding: 9px 14px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: #e5e7eb;
            color: #111827;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .btn:hover { text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: var(--danger-hover); }

        .flash {
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid transparent;
            margin-bottom: 12px;
        }

        .flash-success {
            background: var(--success-bg);
            color: var(--success-text);
            border-color: #bbf7d0;
        }

        .flash-error {
            background: var(--error-bg);
            color: var(--error-text);
            border-color: #fecaca;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .table th,
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: middle;
        }

        .table th { background: #f8fafc; }
        .table tbody tr:hover { background: #f8fbff; }
        .table tbody tr:last-child td { border-bottom: 0; }

        .actions-inline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            max-width: 520px;
        }

        .muted { color: var(--muted); }
    </style>
</head>
<body>
<header>
    <div class="header-bar">
        <strong>Agent Pay System</strong>
        <span class="role-badge">Вход: <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</header>

<main>
    <?php if (!empty($_SESSION['csrf_error'])): ?>
        <p class="flash flash-error"><?= htmlspecialchars((string) $_SESSION['csrf_error'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['csrf_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['app_error'])): ?>
        <p class="flash flash-error"><?= htmlspecialchars((string) $_SESSION['app_error'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php unset($_SESSION['app_error']); ?>
    <?php endif; ?>

    <?= $this->yieldSection('content'); ?>
</main>

<footer>
    Текущий продуктовый этап
</footer>
</body>
</html>