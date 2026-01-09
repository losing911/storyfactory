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

        // Fix Image Paths for DOMPDF (Needs absolute system paths)
        $content = $ebook->content;
        
        // Define the physical path to the public directory
        $publicDir = rtrim(public_path(), '/\\'); 
        
        // Use Regex to find image sources and replace them cleanly
        // Matches src=".../ebooks/..." or src='.../ebooks/...'
        $content = preg_replace_callback(
            '/(src=["\'])(.*?\/ebooks\/)(.*?)(["\'])/i', 
            function($matches) use ($publicDir) {
                // $matches[1] = src="
                // $matches[2] = anything before filename (e.g. /ebooks/ or http://.../ebooks/)
                // $matches[3] = filename (e.g. image.jpg)
                // $matches[4] = closing quote "
                
                // Construct clean local path
                $localPath = $publicDir . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $matches[3];
                
                // Return new src
                return 'src="' . $localPath . '"';
            }, 
            $content
        );
        
        // Same for storage
        $content = preg_replace_callback(
            '/(src=["\'])(.*?\/storage\/)(.*?)(["\'])/i', 
            function($matches) use ($publicDir) {
                $localPath = $publicDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $matches[3];
                return 'src="' . $localPath . '"';
            }, 
            $content
        );

        // Also fix the Cover Image URL if it exists
        $coverPath = null;
        if($ebook->cover_image_url) {
             $coverFilename = basename($ebook->cover_image_url);
             // Check if it's in ebooks or storage
             if(strpos($ebook->cover_image_url, 'ebooks/') !== false) {
                 $coverPath = $publicDir . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $coverFilename;
             } elseif(strpos($ebook->cover_image_url, 'storage/') !== false) {
                 $coverPath = $publicDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $coverFilename;
             } else {
                 // Fallback
                 $coverPath = public_path($ebook->cover_image_url);
             }
        }
        
        // Pass modified content separately
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ebooks.pdf', compact('ebook', 'content', 'coverPath'));
        
        // Setup options for better image handling
        $pdf->setOptions([
            'isRemoteEnabled' => true, 
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
            'chroot' => public_path(), // Security: Allow access to public folder
        ]);

        return $pdf->download($ebook->slug . '.pdf');
    }
}
