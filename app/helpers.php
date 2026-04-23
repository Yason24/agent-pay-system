<?php

use App\Services\AuthService;
use Framework\Core\Http\Response;
use Framework\Core\View\ViewFactory;

function auth(): AuthService
{
    return app(AuthService::class);
}

function redirect(string $location, int $status = 302): Response
{
    return Response::redirect($location, $status);
}

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

function payment_status_label(string $status): string
{
    return match ($status) {
        'paid' => 'Оплачено',
        'pending' => 'В ожидании',
        'failed' => 'Неуспешно',
        default => $status,
    };
}

function formatDate(?string $value): string
{
    if (!$value) {
        return '';
    }
    try {
        return (new DateTime($value))->format('d.m.Y');
    } catch (\Throwable $e) {
        return '';
    }
}

function formatDateTime(?string $value): string
{
    if (!$value) {
        return '';
    }
    try {
        return (new DateTime($value))->format('d.m.Y H:i');
    } catch (\Throwable $e) {
        return '';
    }
}

