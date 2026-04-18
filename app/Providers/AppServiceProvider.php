<?php

namespace App\Providers;

use Framework\Core\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // bindings
    }

    public function boot(): void
    {
        dd('APP PROVIDER BOOTED');
    }
}