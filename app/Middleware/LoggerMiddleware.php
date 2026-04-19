<?php

namespace App\Middleware;

use Closure;
use Framework\Core\Request;
use Framework\Core\Middleware\Middleware;

class LoggerMiddleware
{
    public function handle($request, $next)
    {
        file_put_contents(
            ROOT.'/storage/log.txt',
            "BEFORE\n",
            FILE_APPEND
        );

        $response = $next($request);

        file_put_contents(
            ROOT.'/storage/log.txt',
            "AFTER\n",
            FILE_APPEND
        );

        return $response;
    }
}