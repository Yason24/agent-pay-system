<?php

use Framework\Core\View\ViewFactory;

function view(string $view, array $data = [])
{
    return app(\Framework\Core\View\ViewFactory::class)
        ->make($view, $data);
}