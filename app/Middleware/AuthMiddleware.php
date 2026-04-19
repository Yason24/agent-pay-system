<?php

namespace App\Middleware;

use App\Services\AuthService;
use Closure;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app(AuthService::class)->guest()) {
            return Response::redirect('/login');
        }

        return $next($request);
    }
}