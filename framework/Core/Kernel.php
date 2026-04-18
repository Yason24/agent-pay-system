<?php

namespace Framework\Core;

class Kernel
{
    protected Container $container;
    protected Router $router;

    /*
    |--------------------------------------------------------------------------
    | Global Middleware
    |--------------------------------------------------------------------------
    */

    protected array $middleware = [
        \Yason\WebsiteTemplate\Middleware\LoggerMiddleware::class,
    ];

    public function __construct(Container $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    public function handle(Request $request)
    {
        $pipeline = new Pipeline($this->container);

        return $pipeline
            ->send($request)
            ->through($this->middleware)
            ->then(fn () => $this->router->dispatch($request->uri()));
    }
}