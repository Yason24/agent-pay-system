<?php

namespace Yason\WebsiteTemplate\Middleware;

use Closure;
use Yason\WebsiteTemplate\Core\Request;
use Yason\WebsiteTemplate\Core\Middleware\Middleware;

class LoggerMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next)
    {
        echo "BEFORE<br>";

        $response = $next($request);

        echo "AFTER<br>";

        return $response;
    }
}