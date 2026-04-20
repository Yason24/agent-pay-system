<?php

namespace Framework\Support\Facades;

abstract class Facade
{
    protected static $app;

    public static function setFacadeApplication($app)
    {
        static::$app = $app;
    }

    protected static function getFacadeRoot()
    {
        return static::$app->make(
            static::getFacadeAccessor()
        );
    }

    public static function __callStatic($method, $args)
    {
        return static::getFacadeRoot()
            ->$method(...$args);
    }

    abstract protected static function getFacadeAccessor();
}