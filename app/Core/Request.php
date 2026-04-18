<?php

namespace Yason\WebsiteTemplate\Core;

class Request
{
    public function input(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }

    public function all(): array
    {
        return $_REQUEST;
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}