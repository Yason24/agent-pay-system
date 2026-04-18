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
        // глобальные middleware
        // \App\Middleware\LoggerMiddleware::class,
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
        $this->loadRoutes(); // 🔥 ВАЖНО

        $pipeline = new Pipeline($this->app);

        return $pipeline
            ->send($request)
            ->through($this->middleware)
            ->then(function ($request) {
                $router = $this->app->make(Router::class);

                return $router->dispatch($request->uri());
            });
    }

    protected function loadRoutes(): void
    {
        $router = $this->app->make(\Yason\WebsiteTemplate\Core\Router::class);

        require $this->app->basePath('routes/web.php');
    }
}