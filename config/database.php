<?php

return [

    'driver' => 'pgsql',

    'host' => env('DB_HOST'),

    'port' => env('DB_PORT', '5432'),

    'database' => env('DB_DATABASE'),

    'username' => env('DB_USERNAME'),

    'password' => env('DB_PASSWORD'),

];