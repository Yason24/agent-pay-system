<?php

namespace Yason\WebsiteTemplate\Core;

class Request
{
    protected array $query;
    protected array $request;
    protected array $server;
    protected array $cookies;
    protected array $files;

    public function __construct(
        array $query = [],
        array $request = [],
        array $server = [],
        array $cookies = [],
        array $files = []
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
    }

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

    public static function capture(): static
    {
        return new static(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE,
            $_FILES
        );
    }
}