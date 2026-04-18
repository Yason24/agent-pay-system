<?php

namespace Framework\Core\Http;

use Framework\Core\Application;
use Framework\Core\Request;
use Framework\Core\Pipeline;
use Framework\Core\Router;
use Framework\Core\Http\MiddlewareRegistry;


class Kernel
{
    protected Application $app;
    protected Router $router;
    protected MiddlewareRegistry $registry;

    protected array $middleware = [
        \Framework\Core\Http\Middleware\TrustProxies::class,
        \Framework\Core\Http\Middleware\TrimStrings::class,
    ];

    public function __construct($app)
    {
        $this->app = $app;

        $this->router = $app->make(Router::class);

        $this->registry = new MiddlewareRegistry();

        $this->registerMiddleware();
        $this->loadRoutes();
    }

    protected function pipeline(): Pipeline
    {
        return new Pipeline($this->app);
    }

    /*
    |--------------------------------------------------------------------------
    | Handle HTTP Request
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request)
    {
        $response = $this->pipeline()
            ->send($request)
            ->through($this->middleware)
            ->then(fn ($request) =>
            $this->router->dispatch($request->uri())
            );

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

    protected function loadRoutes(): void
    {
        $router = $this->app->make(\Framework\Core\Router::class);

        require $this->app->basePath('routes/web.php');
    }

    public function resolveMiddleware(array $middleware): array
    {
        return $this->registry->resolve($middleware);
    }

    protected function registerMiddleware(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Aliases
        |--------------------------------------------------------------------------
        */

        $this->registry->alias(
            'trust',
            \Framework\Core\Http\Middleware\TrustProxies::class
        );

        /*
        |--------------------------------------------------------------------------
        | Groups
        |--------------------------------------------------------------------------
        */

        $this->registry->group('web', [
            'trust',
        ]);
    }
}