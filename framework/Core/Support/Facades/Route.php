<?php

namespace Yason\WebsiteTemplate\Core\Support\Facades;

class Route extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}