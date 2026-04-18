<?php

namespace Yason\WebsiteTemplate\Core\Http;

use Yason\WebsiteTemplate\Core\Application;
use Yason\WebsiteTemplate\Core\Request;
use Yason\WebsiteTemplate\Core\Pipeline;
use Yason\WebsiteTemplate\Core\Router;

class Kernel
{
    protected Application $app;

    protected array $middleware = [
        \Yason\WebsiteTemplate\Core\Http\Middleware\TrustProxies::class,
        \Yason\WebsiteTemplate\Core\Http\Middleware\TrimStrings::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /*
    |--------------------------------------------------------------------------
    | Handle HTTP Request
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request)
    {
        $this->loadRoutes();

        $pipeline = new Pipeline($this->app);

        return $pipeline
            ->send($request)
            ->through($this->middleware)
            ->then(function ($request) {

                $router = $this->app->make('router');

                return $router->dispatch($request->uri());
            });
    }

    protected function loadRoutes(): void
    {
        $router = $this->app->make(\Yason\WebsiteTemplate\Core\Router::class);

        require $this->app->basePath('routes/web.php');
    }

    protected array $middlewareGroups = [
        'web' => [
            \Yason\WebsiteTemplate\Core\Http\Middleware\WebMiddleware::class,
        ],
    ];

    protected array $routeMiddleware = [
        'auth' => \App\Http\Middleware\AuthMiddleware::class,
    ];

    public function resolveMiddleware(array $middleware): array
    {
        $resolved = [];

        foreach ($middleware as $name) {

            // group
            if (isset($this->middlewareGroups[$name])) {
                $resolved = array_merge(
                    $resolved,
                    $this->middlewareGroups[$name]
                );
                continue;
            }

            // alias
            if (isset($this->routeMiddleware[$name])) {
                $resolved[] = $this->routeMiddleware[$name];
                continue;
            }

            // already class
            $resolved[] = $name;
        }

        return $resolved;
    }
}