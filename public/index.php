<?php

use Framework\Core\Application;
use Framework\Core\Request;
use Framework\Core\Kernel;

define('ROOT', dirname(__DIR__));
define('BASE_PATH', ROOT);

require ROOT.'/vendor/autoload.php';

/*$app = new Application(BASE_PATH);*/
$app = new Application(ROOT);

$request = Request::capture();

$kernel = $app->make(Kernel::class);

$response = $kernel->handle($request);

$response->send();