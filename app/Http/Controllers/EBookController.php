<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EBookController extends Controller
{
    public function index()
    {
        $books = \App\Models\EBook::where('is_published', true)
            ->orderBy('volume_number', 'desc')
            ->get();
            
        return view('ebooks.index', compact('books'));
    }

    public function show($slug)
    {
        $ebook = \App\Models\EBook::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
            
        return view('ebooks.show', compact('ebook'));
    }
}
