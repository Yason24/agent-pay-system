<?php

namespace Yason\WebsiteTemplate\Providers;

use Yason\WebsiteTemplate\Core\Support\ServiceProvider;
use Yason\WebsiteTemplate\Core\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
        | Bind Request as singleton
        */
        $this->app->singleton(Request::class, function () {
            return new Request();
        });
    }

    public function boot(): void
    {
        //
    }
}