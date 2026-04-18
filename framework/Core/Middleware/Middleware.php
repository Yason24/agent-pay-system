<?php

namespace Framework\Core\Middleware;

use Closure;
use Framework\Core\Request;

interface Middleware
{
    public function handle(Request $request, Closure $next);
}