<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminEBookController;
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
    
    // Explicit Sitemap Route (Backup)
    Route::get('/sitemap.xml', function() {
        return response()->file(public_path('sitemap.xml'), [
            'Content-Type' => 'text/xml'
        ]);
    });

    return view('welcome', compact('stories', 'latestStory', 'spotlightLore', 'stats'));
})->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/story/{story}', [App\Http\Controllers\StoryController::class, 'show'])->name('story.show');

Route::get('/about', function () {
    return view('about');
})->name('about');

// Legal Pages (AdSense Requirement)
Route::get('/legal/privacy-policy', function () { return view('pages.privacy'); })->name('legal.privacy');
Route::get('/legal/terms-of-service', function () { return view('pages.terms'); })->name('legal.terms');
Route::get('/legal/cookie-policy', function () { return view('pages.cookies'); })->name('legal.cookies');
Route::get('/contact', function () { return view('pages.contact'); })->name('contact');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

// Lore / Wiki System
Route::get('/database', [App\Http\Controllers\LoreController::class, 'index'])->name('lore.index');
Route::get('/database/{slug}', [App\Http\Controllers\LoreController::class, 'show'])->name('lore.show');

// E-Books    // Library Routes
    Route::get('/kutuphane', [App\Http\Controllers\EBookController::class, 'index'])->name('ebooks.index');
    Route::get('/kutuphane/{slug}', [App\Http\Controllers\EBookController::class, 'show'])->name('ebooks.show');
    Route::get('/kutuphane/{slug}/download', [App\Http\Controllers\EBookController::class, 'download'])->name('ebooks.download');
    
    // Author Routes
    Route::get('/yazarlar', [App\Http\Controllers\AuthorController::class, 'index'])->name('authors.index');
    Route::get('/author/{slug}', [App\Http\Controllers\AuthorController::class, 'show'])->name('author.show');

// Gallery
Route::get('/gallery', [App\Http\Controllers\GalleryController::class, 'index'])->name('gallery.index');
Route::view('/kronikler', 'chronicles.index')->name('chronicles.index');

// Voting System
Route::get('/poll/active', [App\Http\Controllers\PollController::class, 'getActivePoll']);
Route::post('/poll/vote', [App\Http\Controllers\PollController::class, 'vote']);

// Comment System
Route::post('/comment/store/{story}', [App\Http\Controllers\CommentController::class, 'store'])->name('comment.store');

// Reaction System
Route::post('/story/{story}/react', [App\Http\Controllers\StoryController::class, 'react'])->name('story.react');

// Audio System
Route::get('/story/{story}/audio', [App\Http\Controllers\StoryController::class, 'getAudio'])->name('story.audio');

// Language Switcher
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['tr', 'en'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('stories/{story}/publish', [AdminController::class, 'publish'])->name('stories.publish');
    Route::resource('stories', AdminController::class);
    Route::post('stories/{story}/regenerate-images', [AdminController::class, 'regenerateImages'])->name('stories.regenerate');
    Route::post('stories/{story}/regenerate-chunk', [AdminController::class, 'regenerateImageChunk'])->name('stories.regenerate-chunk');
    Route::get('ai/create', [AdminController::class, 'createAI'])->name('ai.create');
    Route::get('ai/generate', function() { return redirect()->route('admin.ai.create'); });
    Route::post('ai/generate', [AdminController::class, 'generateAI'])->name('ai.generate');
    
    // Chunked Generation Routes
    Route::post('ai/step/story', [AdminController::class, 'generateStoryStep'])->name('ai.step.story');
    Route::post('ai/step/image', [AdminController::class, 'generateImageStep'])->name('ai.step.image');
    Route::post('ai/step/store', [AdminController::class, 'storeStoryStep'])->name('ai.step.store');

    // E-Book Generator Routes
    Route::get('ebooks/create', [AdminEBookController::class, 'create'])->name('ebooks.create');
    Route::post('ebooks/init', [AdminEBookController::class, 'initGeneration'])->name('ebooks.init');
    Route::post('ebooks/chunk', [AdminEBookController::class, 'processChunk'])->name('ebooks.chunk');
    Route::post('ebooks/finalize', [AdminEBookController::class, 'finalize'])->name('ebooks.finalize');

    // Profile Management
    Route::get('/profile', [AdminController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');

    // Author Management
    Route::resource('authors', App\Http\Controllers\AdminAuthorController::class);

    // Lore Management
    Route::resource('lore', App\Http\Controllers\AdminLoreController::class);

    // Inbox / Contact Messages
    Route::get('/inbox', [App\Http\Controllers\AdminContactController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/{id}', [App\Http\Controllers\AdminContactController::class, 'show'])->name('inbox.show');
    Route::delete('/inbox/{id}', [App\Http\Controllers\AdminContactController::class, 'destroy'])->name('inbox.destroy');

    // Newsletter Management
    Route::resource('newsletter', App\Http\Controllers\AdminNewsletterController::class);
    Route::post('newsletter/{id}/send', [App\Http\Controllers\AdminNewsletterController::class, 'send'])->name('newsletter.send');
    Route::get('subscribers', [App\Http\Controllers\AdminNewsletterController::class, 'subscribers'])->name('newsletter.subscribers');
    Route::delete('subscribers/{id}', [App\Http\Controllers\AdminNewsletterController::class, 'destroySubscriber'])->name('newsletter.subscribers.destroy');
});

// Newsletter Public Routes
Route::post('/subscribe', [App\Http\Controllers\SubscriberController::class, 'store'])->name('subscribe.store');
Route::get('/unsubscribe/{token}', [App\Http\Controllers\SubscriberController::class, 'unsubscribe'])->name('subscribe.unsubscribe');

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
Route::get('/debug-story', function() {
    try {
        $story = App\Models\Story::where('durum', 'published')->latest()->first();
        if(!$story) return "No published stories found.";

        echo "<h1>Debug Report for Story ID: {$story->id}</h1>";
        
        // 1. Check Columns
        echo "<h2>1. Raw Attributes</h2>";
        dump($story->getAttributes());

        // 2. Check Author Relationship
        echo "<h2>2. Author Relationship</h2>";
        try {
            echo "Author ID: " . ($story->author_id ?? 'NULL') . "<br>";
            $author = $story->author;
            echo "Author Loaded: " . ($author ? $author->name : 'NULL (No relation)') . "<br>";
        } catch (\Exception $e) {
            echo "<span style='color:red'>AUTHOR ERROR: " . $e->getMessage() . "</span><br>";
        }

        // 3. Check SEO Accessors
        echo "<h2>3. SEO Accessors</h2>";
        try {
            echo "SEO Title: " . $story->seo_title . "<br>";
            echo "SEO Desc: " . $story->seo_description . "<br>";
        } catch (\Exception $e) {
            echo "<span style='color:red'>SEO ERROR: " . $e->getMessage() . "</span><br>";
        }

        // 4. Check Processed Content (Lore Linking)
        echo "<h2>4. Processed Content (Lore)</h2>";
        try {
            // Debug the Cache / Patterns
            $patterns = \Illuminate\Support\Facades\Cache::get('lore_patterns');
            echo "Cached Patterns Count: " . ($patterns ? count($patterns) : 'NULL (Cache Empty)') . "<br>";
            
            if(!$patterns) {
                echo "Re-fetching patterns directly...<br>";
                $entries = \App\Models\LoreEntry::where('is_active', true)->get(['title', 'slug', 'keywords']);
                echo "Active DB Entries: " . $entries->count() . "<br>";
                if($entries->count() > 0) {
                    foreach($entries->take(3) as $e) {
                         echo "Entry: {$e->title} (Slug: {$e->slug})<br>";
                    }
                }
            } else {
                echo "<h3>First 3 Patterns:</h3>";
                foreach(array_slice($patterns, 0, 3) as $p) {
                    echo "Pattern: " . htmlspecialchars($p['pattern']) . "<br>";
                }
            }

            $content = $story->processed_content;
            echo "<h3>Result Content Sample:</h3>";
            echo "<div style='border:1px solid #ccc; padding:10px; background:#f0f0f0;'>";
            echo nl2br(substr(htmlspecialchars($content), 0, 1000));
            echo "</div>";
            
        } catch (\Exception $e) {
            echo "<span style='color:red'>LORE PROC ERROR: " . $e->getMessage() . "</span><br>";
            echo "Trace: " . $e->getTraceAsString();
        }

        return "<hr>End of Report";

    } catch (\Exception $e) {
        return "CRITICAL FAILURE: " . $e->getMessage() . "<br>" . $e->getTraceAsString();
    }
});



Route::get('/debug-pdf-html', function() {
    $ebook = App\Models\EBook::where('is_published', true)->latest()->first();
    if (!$ebook) return "No EBook found.";

    // Logic from Controller (Regex)
    $content = $ebook->content;
    $publicDir = rtrim(public_path(), '/\\'); 
    
    // Debug info
    echo "<h1>Debug Info (Regex Mode)</h1>";
    echo "Public Dir: " . $publicDir . "<br>";
    
    // Regex for Ebooks
    $content = preg_replace_callback(
        '/(src=["\'])(.*?\/ebooks\/)(.*?)(["\'])/i', 
        function($matches) use ($publicDir) {
            $filename = $matches[3];
            $candidates = [
                $publicDir . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $filename,
                str_replace('/public', '', $publicDir) . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $filename,
                str_replace('/public_html/public', '/public_html', $publicDir) . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $filename,
            ];

            foreach ($candidates as $index => $path) {
                 $checkPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                 if (file_exists($checkPath)) {
                     echo "Match Found (Candidate $index): " . $checkPath . "<br>";
                     return 'src="' . $checkPath . '"';
                 }
            }
            
            echo "NO FILE FOUND! Tried: <br>" . implode("<br>", $candidates) . "<br>";
            return 'src="' . $candidates[0] . '"';
        }, 
        $content
    );
    
    echo "<hr><h1>View Render:</h1>";
    
    return view('ebooks.pdf', compact('ebook', 'content'));
});

Route::get('/debug-smtp', function() {
    try {
        echo "<h1>SMTP Debugger</h1>";
        echo "Attempting to send test email...<br>";
        
        \Illuminate\Support\Facades\Mail::raw('This is a test email from Anxipunk Debugger. If you see this, SMTP is working.', function($msg) {
            $msg->to('shtsus@gmail.com') // User requested address
                ->subject('SMTP Connection Test');
        });

        echo "<span style='color:green'>SUCCESS! Email sent successfully. Check your inbox (and spam).</span>";
        echo "<br>If you received this but not the newsletter, your Queue Worker is probably not running.";
        
    } catch (\Exception $e) {
        echo "<span style='color:red'>FAILURE: " . $e->getMessage() . "</span>";
        echo "<h3>Debug Trace:</h3><pre>" . $e->getTraceAsString() . "</pre>";
    }
});
