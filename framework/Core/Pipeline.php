<?php

namespace Yason\WebsiteTemplate\Core;

use Closure;

class Pipeline
{
    protected array $pipes = [];
    protected $passable;
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function send($passable): self
    {
        $this->passable = $passable;
        return $this;
    }

    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            fn($next, $pipe) =>
            function ($passable) use ($next, $pipe) {

                $middleware = $this->container->make($pipe);

                return $middleware->handle($passable, $next);
            },
            $destination
        );

        return $pipeline($this->passable);
    }
}