<?php

define('ROOT', dirname(__DIR__));

require ROOT.'/vendor/autoload.php';

use Framework\Core\Application;
use Framework\Core\Request;
use Framework\Core\Http\Kernel;

$app = new Application(ROOT);

$app = new Framework\Core\Application(
    dirname(__DIR__)
);

$app->boot();

$request = Request::capture();

$kernel = $app->make(Kernel::class);

$response = $kernel->handle($request);

$response->send();