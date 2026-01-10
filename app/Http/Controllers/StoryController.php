<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\BotDetector;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    protected BotDetector $botDetector;

    public function __construct(BotDetector $botDetector)
    {
        $this->botDetector = $botDetector;
    }

    public function show(Request $request, Story $story)
    {
        if ($story->durum !== 'published' && !auth()->check()) {
            abort(404);
        }

        // Only increment view count for REAL visitors (not bots)
        $userAgent = $request->header('User-Agent');
        if (!$this->botDetector->isBot($userAgent)) {
            $story->increment('views');
        }

        // Fetch 3 random suggested stories (excluding current)
        $similarStories = Story::where('durum', 'published')
            ->where('id', '!=', $story->id)
            ->inRandomOrder()
            ->take(3)
            ->get();

        // Fetch Reactions
        $reactions = \Illuminate\Support\Facades\DB::table('story_reactions')
            ->select('reaction_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->where('story_id', $story->id)
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type')
            ->toArray();
        
        // Ensure all types exist
        $reactionTypes = ['overload', 'link', 'flatline'];
        foreach($reactionTypes as $type) {
            if(!isset($reactions[$type])) $reactions[$type] = 0;
        }

        return view('story.show', compact('story', 'similarStories', 'reactions'));
    }

    public function react(Request $request, Story $story)
    {
        $request->validate([
            'type' => 'required|in:overload,link,flatline'
        ]);

        $ip = $request->ip();
        $type = $request->input('type');

        // Check if already reacted with this type
        $exists = \Illuminate\Support\Facades\DB::table('story_reactions')
            ->where('story_id', $story->id)
            ->where('ip_address', $ip)
            ->where('reaction_type', $type)
            ->exists();

        if ($exists) {
            // Remove reaction (Toggle off)
             \Illuminate\Support\Facades\DB::table('story_reactions')
                ->where('story_id', $story->id)
                ->where('ip_address', $ip)
                ->where('reaction_type', $type)
                ->delete();
                
            $action = 'removed';
        } else {
            // Add reaction
            \Illuminate\Support\Facades\DB::table('story_reactions')->insert([
                'story_id' => $story->id,
                'reaction_type' => $type,
                'ip_address' => $ip,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $action = 'added';
        }

        // Return new counts
        $newCounts = \Illuminate\Support\Facades\DB::table('story_reactions')
            ->select('reaction_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->where('story_id', $story->id)
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type');

        return response()->json([
            'status' => 'success',
            'action' => $action,
            'counts' => $newCounts
        ]);
    }
}
