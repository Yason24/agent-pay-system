<?php

namespace Framework\Core\Http;

use Framework\Core\Application;
use Framework\Core\Http\MiddlewareRegistry;
use Framework\Core\Pipeline;
use Framework\Core\Request;
use Framework\Core\Router;

class Kernel
{
    protected Application $app;
    protected Router $router;
    protected MiddlewareRegistry $registry;

    protected array $middleware = [
        \Framework\Core\Http\Middleware\TrustProxies::class,
        \Framework\Core\Http\Middleware\TrimStrings::class,
        \Framework\Core\Http\Middleware\StartSession::class,
    ];

    public function __construct($app)
    {
        $this->app = $app;
        $this->router = $app->make(Router::class);
        $this->registry = new MiddlewareRegistry();

        $this->registerMiddleware();
    }

    protected function pipeline(): Pipeline
    {
        return new Pipeline($this->app);
    }

    public function handle(Request $request)
    {
        $route = $this->router->match(
            $request->method(),
            $request->uri()
        );

        $middleware = array_merge(
            $this->middleware,
            $this->resolveMiddleware($route['middleware'] ?? [])
        );

        $response = $this->pipeline()
            ->send($request)
            ->through($middleware)
            ->then(fn (Request $request) => $this->router->dispatch(
                $request->method(),
                $request->uri()
            ));

        return $this->prepareResponse($response);
    }

    protected function prepareResponse($response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if (is_array($response)) {
            return new Response(
                json_encode($response),
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response((string) $response);
    }


    public function resolveMiddleware(array $middleware): array
    {
        return $this->registry->resolve($middleware);
    }

    protected function registerMiddleware(): void
    {
        $this->registry->alias(
            'trust',
            \Framework\Core\Http\Middleware\TrustProxies::class
        );

        $this->registry->alias(
            'web',
            \Framework\Core\Http\Middleware\WebMiddleware::class
        );

        $this->registry->alias(
            'auth',
            \App\Middleware\AuthMiddleware::class
        );

        $this->registry->alias(
            'guest',
            \App\Middleware\GuestMiddleware::class
        );

        $this->registry->group('web', [
            'trust',
            \Framework\Core\Http\Middleware\WebMiddleware::class,
        ]);
    }
}