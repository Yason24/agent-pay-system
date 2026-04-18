<?php

namespace Yason\WebsiteTemplate\Controllers;

use Yason\WebsiteTemplate\Core\Controller;
use Yason\WebsiteTemplate\Core\Database;
use Yason\WebsiteTemplate\Models\Agent;

class HomeController extends Controller
{
    public function __construct(Database $db)
    {
        echo "Database подключена 🚀<br>";
    }

    public function index(): void
    {
        $agent = Agent::first();

        dd($agent->user);
    }
}