<?php

namespace App\Http\Controllers;

use App\Models\TopicPoll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PollController extends Controller
{
    public function getActivePoll()
    {
        // Get poll for TOMORROW
        $targetDate = Carbon::tomorrow();
        
        $poll = TopicPoll::whereDate('target_date', $targetDate)->first();

        // If no poll exists, create a dummy one (or in production, allow AI to create it)
        // For now, let's create a placeholder if missing
        if (!$poll) {
            $poll = TopicPoll::create([
                'target_date' => $targetDate,
                'options' => [
                    ['id' => 1, 'text' => 'The AI Uprising: A domestic bot goes rogue', 'votes' => 0],
                    ['id' => 2, 'text' => 'Neon Rain: Acid rain floods the lower sector', 'votes' => 0],
                    ['id' => 3, 'text' => 'Memory Dealer: Stolen thoughts on the black market', 'votes' => 0],
                ]
            ]);
        }

        return response()->json($poll);
    }

    public function vote(Request $request)
    {
        $request->validate([
            'poll_id' => 'required|exists:topic_polls,id',
            'option_id' => 'required|integer'
        ]);

        $ip = $request->ip();
        $pollId = $request->poll_id;
        $cacheKey = "voted_{$pollId}_{$ip}";

        if (Cache::has($cacheKey)) {
            return response()->json(['error' => 'ALREADY_VOTED'], 403);
        }

        $poll = TopicPoll::find($pollId);
        $options = $poll->options;
        $found = false;

        foreach ($options as &$opt) {
            if ($opt['id'] == $request->option_id) {
                $opt['votes']++;
                $found = true;
                break;
            }
        }

        if ($found) {
            $poll->options = $options;
            $poll->save();
            
            // Block voting for 24 hours
            Cache::put($cacheKey, true, 60 * 24);
            
            return response()->json(['success' => true, 'options' => $poll->options]);
        }

        return response()->json(['error' => 'INVALID_OPTION'], 400);
    }
}
