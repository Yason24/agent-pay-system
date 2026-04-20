<?php

use Framework\Core\Application;

return function (string $basePath) {
    return new Application($basePath);
};