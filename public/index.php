<?php

use Framework\Core\Application;
use Framework\Core\Request;
use Framework\Core\Kernel;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH.'/vendor/autoload.php';

$app = require BASE_PATH.'/bootstrap/app.php';

$request = Framework\Core\Request::capture();

$response = $app
    ->make(Framework\Core\Http\Kernel::class)
    ->handle($request);

$response->send();