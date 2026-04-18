<?php

namespace Framework\Core\Http\Middleware;

use Closure;
use Framework\Core\Request;

class WebMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // пока просто пропускаем запрос дальше

        return $next($request);
    }
}