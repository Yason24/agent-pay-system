<?php

namespace Framework\Core;

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
        return $this->request[$key]
            ?? $this->query[$key]
            ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->input($key);
        }

        return $data;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function uri(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
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