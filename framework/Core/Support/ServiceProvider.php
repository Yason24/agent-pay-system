<?php

namespace Framework\Core\Support;

use Framework\Core\Container;

abstract class ServiceProvider
{
    protected Container $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /*
    |--------------------------------------------------------------------------
    | Register bindings
    |--------------------------------------------------------------------------
    */
    public function register(): void
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Boot after all providers registered
    |--------------------------------------------------------------------------
    */
    public function boot(): void
    {
    }
}