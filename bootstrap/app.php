<?php

use Framework\Core\Application;

require BASE_PATH.'/vendor/autoload.php';

$app = new Application(BASE_PATH);

$app->boot();

return $app;