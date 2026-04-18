<?php

namespace Yason\WebsiteTemplate\Core\Middleware;

use Closure;
use Yason\WebsiteTemplate\Core\Request;

interface Middleware
{
    public function handle(Request $request, Closure $next);
}