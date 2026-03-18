<?php

use App\Http\Controllers\Api\PhotoLookupController;
use Illuminate\Support\Facades\Route;

Route::post('/photos/lookup', PhotoLookupController::class);
