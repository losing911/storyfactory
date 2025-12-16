<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API Routes
Route::get('/stories/latest', [ApiController::class, 'latestStory']);
Route::get('/stories', [ApiController::class, 'stories']);
Route::get('/lore', [ApiController::class, 'lore']);

