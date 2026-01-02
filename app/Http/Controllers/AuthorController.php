<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function show($slug)
    {
        $author = \App\Models\Author::where('slug', $slug)->with('stories')->firstOrFail();
        return view('authors.show', compact('author'));
    }
}
