<?php

namespace App\Controllers;

use Framework\Core\Controller;

class HomeController extends Controller
{
    public function index(): string
    {
        return $this->view('home', [
            'message' => 'Framework works',
        ]);
    }
}