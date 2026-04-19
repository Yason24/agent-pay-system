<?php

use App\Controllers\HomeController;
use Framework\Core\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
