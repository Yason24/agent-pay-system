<?php

namespace Yason\WebsiteTemplate\Core\Http\Middleware;

use Closure;
use Yason\WebsiteTemplate\Core\Request;

class TrustProxies
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}