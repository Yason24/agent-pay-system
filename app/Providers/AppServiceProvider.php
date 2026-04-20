<?php

namespace App\Providers;

use App\Services\HashService;
use Framework\Support\ServiceProvider;

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