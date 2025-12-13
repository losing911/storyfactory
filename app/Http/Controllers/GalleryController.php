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

        // --- STEP 1: Database Sources (Guaranteed Delivery) ---

        // 1. Lore Images
        $loreEntries = LoreEntry::whereNotNull('image_url')->where('is_active', true)->get();
        foreach ($loreEntries as $lore) {
            $url = $lore->image_url;
            if (!isset($seenUrls[$url])) {
                $images->push((object)[
                    'url' => $url,
                    'title' => $lore->title,
                    'type' => 'Database',
                    'link' => route('lore.show', $lore->slug),
                    'date' => $lore->created_at
                ]);
                $seenUrls[$url] = true;
            }
        }

        // 2. Story Feature & Embedded Images
        $stories = Story::where('durum', 'published')->latest()->get();
        foreach ($stories as $story) {
            // A. Main Cover
            if ($story->gorsel_url && !isset($seenUrls[$story->gorsel_url])) {
                $images->push((object)[
                    'url' => $story->gorsel_url,
                    'title' => $story->getTitle(app()->getLocale()),
                    'type' => 'Story Cover',
                    'link' => route('story.show', $story->slug),
                    'date' => $story->yayin_tarihi
                ]);
                $seenUrls[$story->gorsel_url] = true;
            }

            // B. Embedded Images (Improved Regex)
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $story->metin, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $index => $imgUrl) {
                    if (!isset($seenUrls[$imgUrl])) {
                        $images->push((object)[
                            'url' => $imgUrl,
                            'title' => $story->getTitle(app()->getLocale()) . " (Scene " . ($index + 1) . ")",
                            'type' => 'Story Scene',
                            'link' => route('story.show', $story->slug),
                            'date' => $story->yayin_tarihi
                        ]);
                        $seenUrls[$imgUrl] = true;
                    }
                }
            }
        }

        // --- STEP 2: Filesystem Scan (Add Orphans) ---
        
        $directories = ['stories', 'lore'];
        
        foreach ($directories as $dir) {
            // Try both storage/app/public AND public/storage mapping
            $pathsToCheck = [
                storage_path('app/public/' . $dir),
                public_path('storage/' . $dir)
            ];

            foreach ($pathsToCheck as $path) {
                if (is_dir($path)) {
                    $files = scandir($path);
                    foreach ($files as $file) {
                        if (in_array($file, ['.', '..'])) continue;
                        
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            
                            $url = '/storage/' . $dir . '/' . $file;
                            
                            if (isset($seenUrls[$url])) continue; // Skip duplicates

                            // Infers metadata for orphans
                            $nameParts = explode('_', pathinfo($file, PATHINFO_FILENAME));
                            $slug = $nameParts[0] ?? 'unknown';

                            $images->push((object)[
                                'url' => $url,
                                'title' => ucwords(str_replace('-', ' ', $slug)),
                                'type' => ucfirst($dir) . ' (Unlinked)',
                                'link' => '#',
                                'date' => \Carbon\Carbon::createFromTimestamp(filemtime($path . '/' . $file))
                            ]);
                            $seenUrls[$url] = true;
                        }
                    }
                }
            }
        }

        // Sort by date descending
        $sortedImages = $images->sortByDesc('date')->values();
        
        $perPage = 18;
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
