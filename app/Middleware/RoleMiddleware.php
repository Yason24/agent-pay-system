<?php

namespace App\Middleware;

use App\Services\AuthService;
use Closure;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $auth = app(AuthService::class);
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ($roles !== [] && !$auth->hasAnyRole($roles)) {
            return new Response('Forbidden', 403);
        }

        return $next($request);
    }
}

