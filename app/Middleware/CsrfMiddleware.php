<?php

namespace App\Middleware;

use Closure;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class CsrfMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        if (!$request->isMethod('POST')) {
            return $next($request);
        }

        $submittedToken = (string) $request->input('_token', '');
        $sessionToken = (string) ($_SESSION['_csrf_token'] ?? '');

        if ($submittedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            $_SESSION['csrf_error'] = 'CSRF-токен недействителен или устарел. Повторите действие.';

            $fallback = '/';
            $target = (string) ($_SERVER['HTTP_REFERER'] ?? $fallback);

            return Response::redirect($target);
        }

        return $next($request);
    }
}


