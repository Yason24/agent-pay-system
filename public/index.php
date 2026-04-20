<?php

use Framework\Core\Application;
use Framework\Core\Http\Response;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH.'/vendor/autoload.php';

try {
    $app = require BASE_PATH.'/bootstrap/app.php';

    $request = Framework\Core\Request::capture();

    $response = $app
        ->make(Framework\Core\Http\Kernel::class)
        ->handle($request);
} catch (Throwable $e) {
    error_log((string) $e);

    $response = new Response('Internal Server Error', 500);
}

$response->send();