<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/jobs/pending', [ApiController::class, 'getPendingJobs']);
Route::post('/jobs/complete', [ApiController::class, 'completeJob']);
Route::get('/stories/latest', [ApiController::class, 'getLatestStory']);

// Public API
Route::get('/stories', [ApiController::class, 'index']);
Route::get('/stories/{id}', [ApiController::class, 'show']);
Route::get('/lore', [ApiController::class, 'lore']);
