<?php

namespace Framework\Core\Http\Middleware;

use Closure;
use Framework\Core\Request;

class TrustProxies
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}