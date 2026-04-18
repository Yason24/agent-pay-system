<?php

namespace Framework\Core;

use App\Middleware\LoggerMiddleware;

class Kernel
{
    protected Container $container;
    protected Router $router;

    protected array $middleware = [
        LoggerMiddleware::class,
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