<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\PhotoLookupController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class);
Route::post('/photos/lookup', PhotoLookupController::class);
