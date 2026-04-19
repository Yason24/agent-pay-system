<?php

use Framework\Core\Application;

return function (string $basePath) {

    $app = new Application($basePath);

    $app->bootstrap();

    return $app;
};