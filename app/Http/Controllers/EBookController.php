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
        // Handle both single and double quotes
        $content = $ebook->content;
        $basePath = public_path('ebooks') . '/';
        
        // Replace single quotes src='/ebooks/...'
        $content = str_replace("src='/ebooks/", "src='" . $basePath, $content);
        // Replace double quotes src="/ebooks/..."
        $content = str_replace('src="/ebooks/', 'src="' . $basePath, $content);
        
        // Same for storage if needed
        $storagePath = public_path('storage') . '/';
        $content = str_replace("src='/storage/", "src='" . $storagePath, $content);
        $content = str_replace('src="/storage/', 'src="' . $storagePath, $content);
        
        // Pass modified content separately
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ebooks.pdf', compact('ebook', 'content'));
        
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
