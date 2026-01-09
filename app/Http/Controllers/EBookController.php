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
                $filename = $matches[3];
                
                // Potential paths to check
                $candidates = [
                    // 1. Standard public_path()
                    $publicDir . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $filename,
                    
                    // 2. cPanel Common: public_html IS public (remove /public suffix if present)
                    str_replace('/public', '', $publicDir) . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $filename,
                    
                    // 3. Explicit /public_html/ check (fixing potential double-public issue)
                    str_replace('/public_html/public', '/public_html', $publicDir) . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $filename,
                ];

                foreach ($candidates as $path) {
                     // Normalize slashes for file_exists
                     $checkPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                     if (file_exists($checkPath)) {
                         return 'src="' . $checkPath . '"';
                     }
                }
                
                // Fallback: Return the first candidate even if not found
                return 'src="' . $candidates[0] . '"';
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
