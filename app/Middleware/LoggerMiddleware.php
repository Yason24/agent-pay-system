<?php

namespace App\Middleware;

use Closure;
use Framework\Core\Request;
use Framework\Core\Middleware\Middleware;

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