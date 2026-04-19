<?php

namespace App\Middleware;

use App\Services\AuthService;
use Closure;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app(AuthService::class)->check()) {
            return Response::redirect('/dashboard');
        }

        return $next($request);
    }
}