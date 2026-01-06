<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function show(Story $story)
    {
        if ($story->durum !== 'published' && !auth()->check()) {
            abort(404);
        }

        // Increment view count
        $story->increment('views');

        // Fetch 3 random suggested stories (excluding current)
        $similarStories = Story::where('durum', 'published')
            ->where('id', '!=', $story->id)
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('story.show', compact('story', 'similarStories'));
    }
}
