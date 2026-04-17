<?php

namespace Yason\WebsiteTemplate\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        require dirname(__DIR__) . '/Views/layout.php';
    }
}