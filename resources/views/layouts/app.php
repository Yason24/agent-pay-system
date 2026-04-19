<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Agent Pay System') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f7fb;
            color: #1f2937;
        }

        header, footer {
            background: #111827;
            color: white;
            padding: 16px 24px;
        }

        main {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        form {
            display: grid;
            gap: 12px;
            max-width: 360px;
        }

        input, button {
            padding: 10px 12px;
            font-size: 16px;
        }

        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
    <strong>Agent Pay System</strong>
</header>

<main>
    @yield('content')
</main>

<footer>
    Laravel DNA Phase
</footer>
</body>
</html>