<?php

namespace Yason\WebsiteTemplate\Core\View;

class View
{
    protected string $path;
    protected array $data;

    public function __construct(string $path, array $data = [])
    {
        $this->path = $path;
        $this->data = $data;
    }

    public function render(): string
    {
        if (!file_exists($this->path)) {
            throw new ViewException("View not found: {$this->path}");
        }

        extract($this->data);

        ob_start();

        require $this->path;

        return ob_get_clean();
    }

    public function __toString()
    {
        return $this->render();
    }
}