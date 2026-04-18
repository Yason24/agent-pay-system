<?php

namespace Yason\WebsiteTemplate\Controllers;

use Yason\WebsiteTemplate\Core\Controller;
use Yason\WebsiteTemplate\Core\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        dd($request->method());
    }
}