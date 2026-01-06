<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = \App\Models\Author::withCount('stories')
            ->orderByDesc('stories_count')
            ->get();
            
        return view('authors.index', compact('authors'));
    }

    public function show($slug)
    {
        $author = \App\Models\Author::where('slug', $slug)->with('stories')->firstOrFail();
        return view('authors.show', compact('author'));
    }
}
