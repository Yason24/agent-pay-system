<?php

namespace App\Controllers;

use Framework\Core\Controller;

class HomeController extends Controller
{
    public function index(): string
    {
        return $this->view('home', [
            'message' => 'Agent Pay System foundation is ready for Level 3.',
        ]);
    }
}