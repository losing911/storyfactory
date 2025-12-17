<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\LoginController;

Route::get('/', function () {
    $stories = App\Models\Story::where('durum', 'published')->latest()->paginate(9);
    $latestStory = App\Models\Story::where('durum', 'published')->latest()->first();
    $spotlightLore = App\Models\LoreEntry::where('is_active', true)->inRandomOrder()->first();
    
    $stats = [
        'total_stories' => App\Models\Story::count(),
        'active_nodes' => rand(340, 999), // Mock stat for atmosphere
        'glitches_prevented' => rand(1200, 5000)
    ];

    return view('welcome', compact('stories', 'latestStory', 'spotlightLore', 'stats'));
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/story/{story}', function (App\Models\Story $story) {
    if($story->durum !== 'published') abort(404);
    return view('story.show', compact('story'));
})->name('story.show');

Route::get('/about', function () {
    return view('about');
})->name('about');

// Lore / Wiki System
Route::get('/database', [App\Http\Controllers\LoreController::class, 'index'])->name('lore.index');
Route::get('/database/{slug}', [App\Http\Controllers\LoreController::class, 'show'])->name('lore.show');

// Gallery
Route::get('/gallery', [App\Http\Controllers\GalleryController::class, 'index'])->name('gallery.index');

// Voting System
Route::get('/poll/active', [App\Http\Controllers\PollController::class, 'getActivePoll']);
Route::post('/poll/vote', [App\Http\Controllers\PollController::class, 'vote']);

// Comment System
Route::post('/comment/store/{story}', [App\Http\Controllers\CommentController::class, 'store'])->name('comment.store');

// Language Switcher
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['tr', 'en'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('stories', AdminController::class);
    Route::get('ai/create', [AdminController::class, 'createAI'])->name('ai.create');
    Route::get('ai/generate', function() { return redirect()->route('admin.ai.create'); });
    Route::post('ai/generate', [AdminController::class, 'generateAI'])->name('ai.generate');
    
    // Chunked Generation Routes
    Route::post('ai/step/story', [AdminController::class, 'generateStoryStep'])->name('ai.step.story');
    Route::post('ai/step/image', [AdminController::class, 'generateImageStep'])->name('ai.step.image');
    Route::post('ai/step/store', [AdminController::class, 'storeStoryStep'])->name('ai.step.store');

    // Profile Management
    Route::get('/profile', [AdminController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');

    // Lore Management
    Route::resource('lore', App\Http\Controllers\AdminLoreController::class);
});

// DEBUG
Route::get('/debug-locale', function() {
    // Get the latest story that appears on homepage (ID 14 in screenshot)
    $story = App\Models\Story::latest()->first(); 
    return [
        'app_locale' => app()->getLocale(),
        'session_locale' => session('locale'),
        'story_id' => $story->id ?? null,
        'translations_count' => $story->translations->count() ?? 0,
        'title_en' => $story->getTitle('en'),
    ];
});

Route::get('/fix-stories', function() {
    // Find published stories that still have placeholders
    $stories = App\Models\Story::where('durum', 'published')
        ->where('metin', 'LIKE', '%placehold.co%')
        ->get();
        
    $count = 0;
    foreach ($stories as $story) {
        $story->durum = 'pending_visuals';
        $story->save();
        $count++;
    }
    
    return "Reset $count stories to 'pending_visuals'. Worker will now fix them!";
});

Route::get('/debug-pending', function() {
    $story = App\Models\Story::where('durum', 'pending_visuals')->first();
    if (!$story) return "No stories with status 'pending_visuals' found.";
    
    $placeholderSign = "https://placehold.co/1280x720/1f2937/00ff00";
    $hasPlaceholder = strpos($story->metin, $placeholderSign) !== false;
    
    preg_match('/src=[\'"]' . preg_quote($placeholderSign, '/') . '.*?[\'"].*?alt=[\'"]Scene (\d+)[\'"]/s', $story->metin, $matches);
    
    return [
        'id' => $story->id,
        'title' => $story->baslik,
        'status' => $story->durum,
        'has_placeholder_strpos' => $hasPlaceholder,
        'placeholder_search_term' => $placeholderSign,
        'regex_match_result' => $matches,
        'raw_html_sample' => substr($story->metin, 0, 500) // Show first 500 chars
    ];
});


