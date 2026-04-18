<?php

namespace Yason\WebsiteTemplate\Core\Http\Middleware;

use Closure;
use Yason\WebsiteTemplate\Core\Request;

class WebMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // пока просто пропускаем запрос дальше

        return $next($request);
    }
}