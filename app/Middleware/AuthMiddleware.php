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
        $auth = app(AuthService::class);

        if ($auth->guest()) {
            return redirect('/login');
        }

        $user = $auth->user();

        if ($user && ($user->status ?? 'active') !== 'active') {
            $auth->logout();
            return redirect('/login');
        }

        return $next($request);
    }
}