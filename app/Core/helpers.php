<?php

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

function app()
{
    return \Yason\WebsiteTemplate\Core\Application::getInstance();
}

function config(string $key = null, $default = null)
{
    $config = app()->make(
        \Yason\WebsiteTemplate\Core\Config\ConfigRepository::class
    );

    if (!$key) {
        return $config;
    }

    return $config->get($key, $default);
}