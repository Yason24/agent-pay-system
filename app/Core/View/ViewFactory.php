<?php

namespace Yason\WebsiteTemplate\Core\View;

class ViewFactory
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function make(string $view, array $data = []): string
    {
        $path = $this->viewsPath . '/' .
            str_replace('.', '/', $view) . '.php';

        if (!file_exists($path)) {
            throw new \Exception("View [$view] not found");
        }

        extract($data);

        ob_start();
        require $path;
        return ob_get_clean();
    }
}