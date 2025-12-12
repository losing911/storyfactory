<?php

namespace App\Http\Controllers;

use App\Models\LoreEntry;
use Illuminate\Http\Request;

class LoreController extends Controller
{
    public function index()
    {
        // Group entries by type
        $cities = LoreEntry::where('type', 'city')->where('active', true)->get();
        $factions = LoreEntry::where('type', 'faction')->where('active', true)->get();
        $characters = LoreEntry::where('type', 'character')->where('active', true)->get();

        return view('lore.index', compact('cities', 'factions', 'characters'));
    }

    public function show($slug)
    {
        $entry = LoreEntry::where('slug', $slug)->where('active', true)->firstOrFail();
        
        // Fetch related stories (if we had a relation, for now just 3 random stories)
        // Check if we have a relationship, if not, mock it or leave empty
        $relatedStories = \App\Models\Story::where('durum', 'published')->inRandomOrder()->take(3)->get();

        return view('lore.show', compact('entry', 'relatedStories'));
    }
}
