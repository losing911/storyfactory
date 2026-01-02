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

    public function download($slug)
    {
        $ebook = \App\Models\EBook::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Wrap content in simple HTML structure
        $htmlContent = "<!DOCTYPE html><html><head><title>{$ebook->title}</title><meta charset='utf-8'></head><body>";
        $htmlContent .= "<h1 style='text-align:center'>{$ebook->title}</h1>";
        $htmlContent .= "<p style='text-align:center'>Volume {$ebook->volume_number}</p>";
        $htmlContent .= "<hr>";
        $htmlContent .= $ebook->content;
        $htmlContent .= "</body></html>";

        $fileName = $ebook->slug . '.html';

        return response($htmlContent)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }
}
