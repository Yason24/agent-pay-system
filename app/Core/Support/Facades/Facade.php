<?php

namespace Yason\WebsiteTemplate\Core\Support\Facades;

use Yason\WebsiteTemplate\Core\Application;

abstract class Facade
{
    protected static function getFacadeAccessor()
    {
        throw new \Exception('Facade does not implement accessor');
    }

    protected static function resolveInstance()
    {
        return Application::getInstance()
            ->make(static::getFacadeAccessor());
    }

    public static function __callStatic($method, $args)
    {
        return static::resolveInstance()
            ->$method(...$args);
    }
}