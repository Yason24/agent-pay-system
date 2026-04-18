<?php
namespace Framework\Core\Http\Middleware;

use Closure;
use Framework\Core\Request;

class TrimStrings
{
    public function handle(Request $request, Closure $next)
    {
        $_GET = array_map('trim', $_GET);
        $_POST = array_map('trim', $_POST);

        return $next($request);
    }
}