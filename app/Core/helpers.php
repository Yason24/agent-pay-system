<?php

use Yason\WebsiteTemplate\Core\Application;
function dd($data)
{
    echo '<pre>';
    print_r($data);
    die();
}

function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

function app($abstract = null)
{
    $app = Application::getInstance();

    if ($abstract) {
        return $app->make($abstract);
    }

    return $app;
}

function config(string $key = null)
{
    $config = app()->make('config');

    if (!$key) {
        return $config;
    }

    return $config->get($key);
}