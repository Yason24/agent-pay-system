<?php

namespace Framework\Support;

use Framework\Core\Application;

abstract class ServiceProvider
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}