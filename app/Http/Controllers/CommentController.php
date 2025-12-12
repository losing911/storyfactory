<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Story;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Story $story)
    {
        $request->validate([
            'nickname' => 'nullable|string|max:50',
            'message' => 'required|string|max:1000'
        ]);

        $comment = Comment::create([
            'story_id' => $story->id,
            'nickname' => $request->nickname ?? 'Anonymous Netrunner',
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'is_approved' => true
        ]);

        return response()->json($comment);
    }
}
