<?php

define('ROOT', dirname(__DIR__));

require __DIR__.'/../vendor/autoload.php';

use Framework\Core\Application;
use Framework\Core\Request;
use Framework\Core\Http\Kernel;

$app = new Application(
    dirname(__DIR__)
);

$request = $app->make(Request::class);

$kernel = $app->make(
    \Framework\Core\Http\Kernel::class
);

$response = $kernel->handle($request);

$response->send();