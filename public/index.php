<?php

define('ROOT', dirname(__DIR__));

require ROOT.'/vendor/autoload.php';

use Yason\WebsiteTemplate\Core\Application;
use Yason\WebsiteTemplate\Core\Request;
use Yason\WebsiteTemplate\Core\Http\Kernel;

$app = new Application(ROOT);

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $app->make(Request::class)
);

echo $response;