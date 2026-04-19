<?php

namespace Framework\Core\Http\Middleware;

use Closure;
use Framework\Core\Request;

class StartSession
{
    public function handle(Request $request, Closure $next)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $next($request);
    }
}