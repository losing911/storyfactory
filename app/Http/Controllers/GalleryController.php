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

        // 1. Lore Images
        $loreEntries = LoreEntry::whereNotNull('image_url')->where('is_active', true)->get();
        foreach ($loreEntries as $lore) {
            $images->push((object)[
                'url' => $lore->image_url,
                'title' => $lore->title,
                'type' => 'Database',
                'link' => route('lore.show', $lore->slug),
                'date' => $lore->created_at
            ]);
        }

        // 2. Story Feature Images
        $stories = Story::where('durum', 'published')->latest()->get();
        foreach ($stories as $story) {
            // Main Feature Image
            if ($story->gorsel_url) {
                $images->push((object)[
                    'url' => $story->gorsel_url,
                    'title' => $story->getTitle(app()->getLocale()),
                    'type' => 'Story Cover',
                    'link' => route('story.show', $story->slug),
                    'date' => $story->yayin_tarihi
                ]);
            }

            // Embedded Images in Content
            // Regex to find <img src="...">
            preg_match_all('/<img[^>]+src="([^">]+)"/i', $story->metin, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $index => $imgUrl) {
                    $images->push((object)[
                        'url' => $imgUrl,
                        'title' => $story->getTitle(app()->getLocale()) . " (Scene " . ($index + 1) . ")",
                        'type' => 'Story Scene',
                        'link' => route('story.show', $story->slug),
                        'date' => $story->yayin_tarihi
                    ]);
                }
            }
        }

        // Sort by date descending and paginate manually
        $sortedImages = $images->sortByDesc('date')->values();
        
        $perPage = 12;
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
