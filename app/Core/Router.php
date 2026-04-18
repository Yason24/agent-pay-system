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

    public function get(string $uri, array $action): self
    {
        $this->routes[$uri] = [
            'action' => $action,
            'middleware' => $this->routeMiddleware
        ];

        $this->routeMiddleware = [];

        return $this;
    }

    public function middleware(array $middleware): self
    {
        $this->routeMiddleware = $middleware;

        return $this;
    }

    public function dispatch($uri)
    {
        if (!isset($this->routes[$uri])) {
            die('404');
        }

        $route = $this->routes[$uri];

        [$controller, $method] = $route['action'];

        $request = $this->container->make(Request::class);

        $pipeline = new Pipeline($this->container);

        return $pipeline
            ->send($request)
            ->through($route['middleware'])
            ->then(function ($request) use ($controller, $method) {

                $controllerInstance = $this->container->make($controller);

                return $this->container->call(
                    $controllerInstance,
                    $method
                );
            });
    }
}