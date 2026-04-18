<?php

namespace Framework\Core;

use Framework\Core\Container;
use Framework\Core\Request;
use Framework\Core\Pipeline;

class Router
{
    private array $routes = [];
    protected array $groupStack = [];
    protected array $groupMiddleware = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $uri, array $action, array $middleware = []): void
    {
        $prefix = '';
        $middlewareStack = [];

        foreach ($this->groupStack as $group) {

            $prefix .= $group['prefix'] ?? '';

            if (isset($group['middleware'])) {

                $groupMiddleware = $group['middleware'];

                if (!is_array($groupMiddleware)) {
                    $groupMiddleware = [$groupMiddleware];
                }

                $middlewareStack = array_merge(
                    $middlewareStack,
                    $groupMiddleware
                );
            }
        }

        $this->routes[$prefix . $uri] = [
            'action' => $action,
            'middleware' => $middlewareStack,
        ];
    }

    public function middleware(string|array $middleware): self
    {
        $this->currentMiddleware = (array) $middleware;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        return $this->group([
            'prefix' => $prefix
        ], fn () => $this);
    }

    public function group($attributes, callable $callback = null): self
    {
        if ($attributes instanceof \Closure) {
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

    public function dispatch(string $uri)
    {
        if (!isset($this->routes[$uri])) {
            throw new \Exception('Route not found');
        }

        $route = $this->routes[$uri];

        [$controller, $method] = $route['action'];

        $request = $this->container->make(Request::class);

        $pipeline = new Pipeline($this->container);

        $kernel = $this->container->make(
            \Framework\Core\Http\Kernel::class
        );

        $middleware = $kernel->resolveMiddleware(
            $route['middleware']
        );

        return $pipeline
            ->send($request)
            ->through($middleware)
            ->then(function ($request) use ($controller, $method) {

                $controllerInstance =
                    $this->container->make($controller);

                return $this->container->call(
                    $controllerInstance,
                    $method
                );
            });

    }
}