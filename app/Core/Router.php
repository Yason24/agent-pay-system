<?php

namespace Yason\WebsiteTemplate\Core;

class Router
{
    private array $routes = [];

    private array $routeMiddleware = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $uri, array $action, array $middleware = []): void
    {
        $this->routes[$uri] = [
            'action' => $action,
            'middleware' => $middleware,
        ];
    }

    public function middleware(array $middleware): self
    {
        $this->routeMiddleware = $middleware;

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

        return $pipeline
            ->send($request)
            ->through($route['middleware'])
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