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
        
        // CLEANUP ARTIFACTS: SUPER NUCLEAR OPTION
        // 1. Remove artifacts wrapped in P tags (common in WYSIWYG/Markdown parsers)
        $content = preg_replace('/<p>\s*(```|\'\'\'|&#96;&#96;&#96;|&#39;&#39;&#39;)(?:html)?\s*<\/p>/iu', '', $content);
        
        // 2. Remove artifacts that are part of text lines
        $content = preg_replace('/(```|\'\'\'|&#96;&#96;&#96;|&#39;&#39;&#39;)(?:html)?/iu', '', $content);
        
        // 3. Specific manual kill list for things that might escape Regex
        $content = str_replace([
            "'''html", "'''", "```html", "```", 
            "&#39;&#39;&#39;html", "&#39;&#39;&#39;",
            "&amp;#39;&amp;#39;&amp;#39;html", // Double encoded
        ], '', $content);

        // 4. Clean up empty paragraphs left behind
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        
        // VISUALS: Inject Drop Caps
        // Find paragraphs that follow headers (h2,h3) or the start of a chapter div
        // Pattern: (tag closing) + whitespace + <p> + (first char)
        $content = preg_replace_callback(
            '/((?:<\/h[1-6]>|<\/div>))\s*<p>\s*(.)/u',
            function($matches) {
                // $matches[1] = closing tag (e.g. </h3>)
                // $matches[2] = First letter
                // Check if it's a valid letter/number (to avoid wrapping quotes or spacing)
                if (preg_match('/[\w\p{L}]/u', $matches[2])) {
                    return $matches[1] . '<p><span class="drop-cap">' . $matches[2] . '</span>';
                }
                return $matches[0];
            },
            $content
        );
        
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
                     $checkPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                     if (file_exists($checkPath)) {
                         // Get image type for data URI
                         $type = pathinfo($checkPath, PATHINFO_EXTENSION);
                         $data = file_get_contents($checkPath);
                         $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                         
                         return 'src="' . $base64 . '"';
                     }
                }
                
                // Fallback
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
             
             // Candidates for cover
             $coverCandidates = [
                 $publicDir . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $coverFilename,
                 $publicDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $coverFilename,
                 str_replace('/public', '', $publicDir) . DIRECTORY_SEPARATOR . 'ebooks' . DIRECTORY_SEPARATOR . $coverFilename,
             ];
             
             foreach($coverCandidates as $cPath) {
                 $cPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cPath);
                 if(file_exists($cPath)) {
                     $type = pathinfo($cPath, PATHINFO_EXTENSION);
                     $data = file_get_contents($cPath);
                     $coverPath = 'data:image/' . $type . ';base64,' . base64_encode($data);
                     break;
                 }
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
