<?php

namespace App\Controllers;
namespace Framework\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        require dirname(__DIR__) . '/Views/layout.php';
    }

    public function index()
    {
        return view('home', [
            'message' => 'Framework works 🚀'
        ]);
    }
}