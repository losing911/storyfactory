<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use App\Models\LoreEntry;

class GalleryController extends Controller
{
    public function index()
    {
        $images = collect();
        $seenUrls = [];

        // 1. Scan Filesystem (The most reliable way to get "ALL" images)
        $directories = ['stories', 'lore'];
        
        foreach ($directories as $dir) {
            $path = storage_path('app/public/' . $dir);
            if (is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if (in_array($file, ['.', '..'])) continue;
                    
                    // Check extension
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        
                        $url = '/storage/' . $dir . '/' . $file;
                        
                        // Metadata (Try to infer from filename)
                        // names are often: story-slug_timestamp.jpg or slug.jpg
                        $nameParts = explode('_', pathinfo($file, PATHINFO_FILENAME));
                        $slug = $nameParts[0] ?? 'unknown';
                        
                        $images->push((object)[
                            'url' => $url,
                            'title' => ucwords(str_replace('-', ' ', $slug)),
                            'type' => ucfirst($dir) . ' Archive',
                            'link' => '#', // No direct link reliable if orphaned
                            'date' => \Carbon\Carbon::createFromTimestamp(filemtime($path . '/' . $file))
                        ]);
                        
                        $seenUrls[$url] = true;
                    }
                }
            }
        }

        // 2. Enhance Metadata from DB (Overwrite specific items if found)
        // Lore
        $loreEntries = LoreEntry::whereNotNull('image_url')->where('is_active', true)->get();
        foreach ($loreEntries as $lore) {
             // Find matching image in collection to update metadata
             $match = $images->firstWhere('url', $lore->image_url);
             if ($match) {
                 $match->title = $lore->title;
                 $match->type = 'Database';
                 $match->link = route('lore.show', $lore->slug);
             }
        }

        // Stories
        $stories = Story::where('durum', 'published')->get();
        foreach ($stories as $story) {
            // Main Image matches
            $match = $images->firstWhere('url', $story->gorsel_url);
            if ($match) {
                $match->title = $story->getTitle(app()->getLocale());
                $match->type = 'Story Cover';
                $match->link = route('story.show', $story->slug);
            }
        }

        // Sort by date descending
        $sortedImages = $images->sortByDesc('date')->values();
        
        $perPage = 18; // Increased for better grid
        $page = request()->get('page', 1);
        $paginatedImages = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedImages->forPage($page, $perPage),
            $sortedImages->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('gallery.index', compact('paginatedImages'));
    }
}
