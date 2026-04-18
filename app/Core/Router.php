<?php

namespace Yason\WebsiteTemplate\Core;

class Router
{
    private array $routes = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $uri, array $action): void
    {
        $this->routes[$uri] = $action;
    }

    public function dispatch($uri)
    {
        if (!isset($this->routes[$uri])) {
            die('404');
        }

        [$controller, $method] = $this->routes[$uri];

        $controllerInstance = $this->container->make($controller);

        return $this->container->call(
            $controllerInstance,
            $method
        );
    }
}