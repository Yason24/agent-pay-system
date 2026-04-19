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
    private array $routes = [];
    protected array $groupStack = [];
    protected array $groupMiddleware = [];
    private Container $container;

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
        $this->routes[$method][$uri] = $action;
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

    /*public function dispatch(string $uri)
    {
        if (!isset($this->routes[$uri])) {
            throw new \Exception('Route not found');
        }

        $route = $this->routes[$uri];
        $action = $route['action'];

        if ($action instanceof \Closure) {
            return $action();
        }

        [$controller, $method] = $action;

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
            ->then(function () use ($controller, $method) {

                $controllerInstance =
                    $this->container->make($controller);

                return $this->container->call(
                    $controllerInstance,
                    $method
                );
            });
    }*/

    public function dispatch(string $method, string $uri)
    {
        $action = $this->routes[$method][$uri] ?? null;

        if (!$action) {
            throw new Exception('Route not found');
        }

        // Closure route
        if ($action instanceof Closure) {

            $result = $action();

            return new Response($result);
        }

        // Controller route
        if (is_array($action)) {

            [$controller, $method] = $action;

            $controller = $this->container->make($controller);

            $result = $controller->$method();

            return new Response($result);
        }
    }

}