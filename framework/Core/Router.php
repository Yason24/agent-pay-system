<?php

namespace Framework\Core;

use Closure;
use Exception;
use Framework\Core\Http\Response;

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

    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    protected function addRoute(string $method, string $uri, $action): void
    {
        $routeMiddleware = [];

        if (!empty($this->groupStack)) {
            $prefix = '';

            foreach ($this->groupStack as $group) {
                if (isset($group['prefix'])) {
                    $prefix .= '/' . trim($group['prefix'], '/');
                }

                if (isset($group['middleware'])) {
                    $routeMiddleware = array_merge(
                        $routeMiddleware,
                        (array) $group['middleware']
                    );
                }
            }

            $uri = $prefix . '/' . ltrim($uri, '/');
        }

        $method = strtoupper($method);
        $uri = $this->normalizeUri($uri);

        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $routeMiddleware,
        ];
    }

    public function middleware(string|array $middleware): self
    {
        $this->groupMiddleware = (array) $middleware;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        return $this->group([
            'prefix' => $prefix,
        ], fn () => $this);
    }

    public function group($attributes, ?callable $callback = null): self
    {
        if ($attributes instanceof Closure) {
            $callback = $attributes;
            $attributes = [];
        }

        if (!empty($this->groupMiddleware)) {
            $attributes['middleware'] = array_merge(
                $attributes['middleware'] ?? [],
                $this->groupMiddleware
            );

            $this->groupMiddleware = [];
        }

        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);

        return $this;
    }

    public function match(string $method, string $uri): array
    {
        $method = strtoupper($method);
        $uri = $this->normalizeUri($uri);

        $route = $this->routes[$method][$uri] ?? null;

        if (!$route) {
            throw new Exception('Route not found');
        }

        return $route;
    }

    public function dispatch(string $method, string $uri)
    {
        $route = $this->match($method, $uri);
        $action = $route['action'];

        if ($action instanceof Closure) {
            return new Response($action());
        }

        if (is_array($action)) {
            [$controller, $methodName] = $action;
            $controller = $this->container->make($controller);

            return new Response($this->container->call($controller, $methodName));
        }

        throw new Exception('Invalid route action');
    }

    protected function normalizeUri(string $uri): string
    {
        $normalized = '/' . trim($uri, '/');

        return $normalized === '//' ? '/' : $normalized;
    }
}