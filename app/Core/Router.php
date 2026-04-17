<?php

namespace Yason\WebsiteTemplate\Core;
use Yason\WebsiteTemplate\Core\Container;

class Router
{
    private array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->routes[$uri] = $action;
    }

    public function dispatch(string $requestUri): void
    {
        $uri = parse_url($requestUri, PHP_URL_PATH);

        if (!isset($this->routes[$uri])) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        [$controller, $method] = $this->routes[$uri];

        $container = new Container();

        $controllerInstance = $container->make($controller);

        $controllerInstance->$method();
    }
}