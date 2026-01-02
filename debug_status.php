<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$totalStories = \App\Models\Story::count();
$storiesWithAuthor = \App\Models\Story::whereNotNull('author_id')->count();
$storiesWithoutAuthor = \App\Models\Story::whereNull('author_id')->get();

echo "Total Stories: $totalStories\n";
echo "With Author: $storiesWithAuthor\n";
echo "Without Author: " . $storiesWithoutAuthor->count() . "\n";

if ($storiesWithoutAuthor->count() > 0) {
    echo "Assigning random authors to orphans...\n";
    $authors = \App\Models\Author::all();
    
    if ($authors->count() === 0) {
        echo "No authors found! Creating orphans...\n";
        // Create generated authors
        $genAuthors = [
            ['name' => 'Nexus Ryder', 'role' => 'Netrunner', 'slug' => 'nexus-ryder'],
            ['name' => 'Valkyrie 7', 'role' => 'Street Samurai', 'slug' => 'valkyrie-7'],
            ['name' => 'Ghost Interface', 'role' => 'Glitch Artist', 'slug' => 'ghost-interface'],
        ];
        foreach($genAuthors as $a) {
             \App\Models\Author::create($a);
        }
        $authors = \App\Models\Author::all();
    }

    foreach ($storiesWithoutAuthor as $story) {
        $story->author_id = $authors->random()->id;
        $story->save();
        echo "Assigned " . $story->author->name . " to " . $story->baslik . "\n";
    }
}

// Check E-Books
$ebooks = \App\Models\EBook::all();
echo "\n--- E-BOOKS ---\n";
foreach($ebooks as $eb) {
    echo "ID: {$eb->id} - {$eb->title} (Vol {$eb->volume_number}) - Published: {$eb->is_published}\n";
}
if($ebooks->count() == 0) echo "No E-Books found.\n";
