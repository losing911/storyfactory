<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use App\Models\LoreEntry;

class ApiController extends Controller
{
    /**
     * Get the latest published story.
     */
    public function latestStory()
    {
        $story = Story::where('durum', 'published')
            ->latest('yayin_tarihi')
            ->first();

        if (!$story) {
            return response()->json(['error' => 'No stories found'], 404);
        }

        return response()->json([
            'id' => $story->id,
            'title' => $story->getTitle('tr'), // Default to TR as site is TR
            'slug' => $story->slug,
            'summary' => $story->sosyal_ozet ?? mb_substr(strip_tags($story->metin), 0, 150) . '...',
            'url' => route('story.show', $story->slug),
            'image_url' => $story->gorsel_url ? asset($story->gorsel_url) : null,
            'tags' => $story->etiketler,
            'published_at' => $story->yayin_tarihi,
        ]);
    }

    /**
     * List stories with pagination.
     */
    public function stories()
    {
        $stories = Story::where('durum', 'published')
            ->latest('yayin_tarihi')
            ->paginate(10);

        return response()->json($stories);
    }

    /**
     * Get all lore entries.
     */
    public function lore()
    {
        $lore = LoreEntry::where('is_active', true)->get();
        return response()->json($lore);
    }
}
