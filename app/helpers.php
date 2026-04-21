<?php

use Framework\Core\View\ViewFactory;

function view(string $view, array $data = [])
{
    return app(\Framework\Core\View\ViewFactory::class)
        ->make($view, $data);
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

    return '<input type="hidden" name="_token" value="' . $token . '">';
}
