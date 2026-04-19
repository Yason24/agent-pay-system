<?php

namespace Framework\Core;

use Framework\Core\Container;
use Framework\Core\Pipeline;
use Framework\Core\Request;
use Framework\Core\Http\Response;
use Closure;
use Exception;

class Router
{
    protected array $routes = [];
    protected array $groupStack = [];
    protected array $groupMiddleware = [];
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    protected function addRoute(string $method, string $uri, $action): void
    {
        if (!empty($this->groupStack)) {
            $prefix = '';

            foreach ($this->groupStack as $group) {
                if (isset($group['prefix'])) {
                    $prefix .= '/' . trim($group['prefix'], '/');
                }
            }

            $uri = $prefix . '/' . ltrim($uri, '/');
        }

        $method = strtoupper($method);
        $uri = $this->normalizeUri($uri);

        $this->routes[$method][$uri] = $action;
    }

    public function middleware(string|array $middleware): self
    {
        $this->groupMiddleware = (array) $middleware;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        return $this->group([
            'prefix' => $prefix
        ], fn () => $this);
    }

    public function group($attributes, ?callable $callback = null): self
    {
        if ($attributes instanceof Closure) {
            $callback = $attributes;
            $attributes = [];
        }

        if (!empty($this->groupMiddleware)) {
            $attributes['middleware'] = $this->groupMiddleware;
            $this->groupMiddleware = [];
        }

        $this->groupStack[] = $attributes;

        $callback($this);

        array_pop($this->groupStack);

        return $this;
    }

    public function dispatch(string $method, string $uri)
    {
        $method = strtoupper($method);
        $uri = $this->normalizeUri($uri);
        $action = $this->routes[$method][$uri] ?? null;

        if (!$action) {
            throw new Exception('Route not found');
        }

        if ($action instanceof Closure) {
            return new Response($action());
        }

        if (is_array($action)) {
            [$controller, $methodName] = $action;
            $controller = $this->container->make($controller);

            return new Response($controller->$methodName());
        }

        throw new Exception('Invalid route action');
    }

    protected function normalizeUri(string $uri): string
    {
        $normalized = '/' . trim($uri, '/');

        return $normalized === '//' ? '/' : $normalized;
    }
}