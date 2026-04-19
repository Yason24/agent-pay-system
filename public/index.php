<?php

use Framework\Core\Application;
use Framework\Core\Request;
use Framework\Core\Kernel;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH.'/vendor/autoload.php';

/*$app = new Application(BASE_PATH);*/
$app = new Application(BASE_PATH);

$request = Request::capture();

$kernel = $app->make(Kernel::class);

$response = $kernel->handle($request);

$response->send();