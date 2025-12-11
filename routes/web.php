<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    $stories = App\Models\Story::where('durum', 'published')->latest()->paginate(9);
    return view('welcome', compact('stories'));
})->name('home');

Route::get('/story/{story}', function (App\Models\Story $story) {
    if($story->durum !== 'published') abort(404);
    return view('story.show', compact('story'));
})->name('story.show');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('stories', AdminController::class);
    Route::get('ai/create', [AdminController::class, 'createAI'])->name('ai.create');
    Route::get('ai/generate', function() { return redirect()->route('admin.ai.create'); });
    Route::post('ai/generate', [AdminController::class, 'generateAI'])->name('ai.generate');
});
