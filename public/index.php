<?php

define('ROOT', dirname(__DIR__));

require ROOT.'/vendor/autoload.php';

use Yason\WebsiteTemplate\Core\Application;
use Yason\WebsiteTemplate\Core\Request;
use Yason\WebsiteTemplate\Core\Http\Kernel;

$app = new Application(ROOT);

$request = $app->make(Request::class);

$kernel = $app->make(
    Yason\WebsiteTemplate\Core\Http\Kernel::class
);

$response = $kernel->handle($request);

echo $response;