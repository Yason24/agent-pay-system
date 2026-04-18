<?php

namespace App\Providers;

use Framework\Core\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        echo "APP PROVIDER REGISTERED!";
    }

    public function boot(): void
    {
        echo "APP PROVIDER BOOTED!";
    }
}