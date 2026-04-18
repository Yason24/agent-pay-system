<?php

use Yason\WebsiteTemplate\Core\View\ViewFactory;

function view(string $view, array $data = [])
{
    return app(\Yason\WebsiteTemplate\Core\View\ViewFactory::class)
        ->make($view, $data);
}