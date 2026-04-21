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
    protected array $middlewareAliases = [
        'auth' => 'App\\Middleware\\AuthMiddleware',
        'guest' => 'App\\Middleware\\GuestMiddleware',
        'role' => 'App\\Middleware\\RoleMiddleware',
    ];
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
        $request = $this->container->make(Request::class);

        $destination = function (Request $request) use ($action) {
            if ($action instanceof Closure) {
                $result = $action();

                return $result instanceof Response
                    ? $result
                    : new Response($result);
            }

            if (is_array($action)) {
                [$controller, $methodName] = $action;
                $controller = $this->container->make($controller);
                $result = $this->container->call($controller, $methodName);

                return $result instanceof Response
                    ? $result
                    : new Response($result);
            }

            throw new Exception('Invalid route action');
        };

        $middleware = $this->resolveMiddlewareDefinitions($route['middleware'] ?? []);

        if ($middleware === []) {
            return $destination($request);
        }

        $pipeline = array_reduce(
            array_reverse($middleware),
            fn($next, $pipe) => function (Request $request) use ($next, $pipe) {
                return $this->container->make($pipe['class'])->handle($request, $next, ...$pipe['params']);
            },
            $destination
        );

        return $pipeline($request);
    }

    protected function resolveMiddlewareDefinitions(array $middleware): array
    {
        $resolved = [];

        foreach ($middleware as $definition) {
            $raw = trim((string) $definition);

            if ($raw === '') {
                continue;
            }

            [$name, $paramsRaw] = array_pad(explode(':', $raw, 2), 2, null);
            $name = trim($name);
            $class = $this->middlewareAliases[$name] ?? $name;

            if (!class_exists($class)) {
                throw new Exception('Middleware not found: ' . $class);
            }

            $params = [];

            if ($paramsRaw !== null && trim($paramsRaw) !== '') {
                $params = array_values(array_filter(array_map(
                    static fn(string $value): string => trim($value),
                    explode(',', $paramsRaw)
                ), static fn(string $value): bool => $value !== ''));
            }

            $resolved[] = [
                'class' => $class,
                'params' => $params,
            ];
        }

        return $resolved;
    }

    protected function normalizeUri(string $uri): string
    {
        $normalized = '/' . trim($uri, '/');

        return $normalized === '//' ? '/' : $normalized;
    }
}