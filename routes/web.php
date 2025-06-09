<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// In routes/web.php
Route::post('/optimize-images', [\App\Http\Controllers\ImageController::class, 'optimizeImage']);
