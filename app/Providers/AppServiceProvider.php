<?php

namespace App\Providers;

use Framework\Support\ServiceProvider;
use App\Services\HashService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HashService::class, fn() => new HashService());
    }

    public function boot(): void
    {
        //
    }
}