<?php

namespace Yason\WebsiteTemplate\Controllers;

use Yason\WebsiteTemplate\Core\Controller;
use Yason\WebsiteTemplate\Core\Database;

class HomeController extends Controller
{
    public function __construct(Database $db)
    {
        echo "Database подключена 🚀<br>";
    }

    public function index(): void
    {
        $this->view('home');
    }
}