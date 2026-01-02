<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use App\Models\EBook;
use App\Services\AIService;
use Illuminate\Support\Str;

class AdminEBookController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $ebooks = EBook::latest()->get();
        return view('admin.ebooks.index', compact('ebooks'));
    }

    public function create()
    {
        try {
            $lastBook = EBook::orderBy('volume_number', 'desc')->first();
            $startId = $lastBook ? ($lastBook->end_story_id + 1) : 1;
            $volume = $lastBook ? ($lastBook->volume_number + 1) : 1;
            
            $eligibleStories = Story::where('id', '>=', $startId)
                ->where('durum', 'published')
                ->count();

            // Force Render to trap errors
            return view('admin.ebooks.create', compact('volume', 'startId', 'eligibleStories'))->render();
        } catch (\Exception $e) {
            dd("EBOOK CREATE ERROR: " . $e->getMessage(), $e->getTraceAsString());
        }
    }

    // Step 1: Initialize & Calculate Chunks
    public function initGeneration(Request $request)
    {
        $lastBook = EBook::orderBy('volume_number', 'desc')->first();
        $startId = $lastBook ? ($lastBook->end_story_id + 1) : 1;
        $volume = $lastBook ? ($lastBook->volume_number + 1) : 1;

        $stories = Story::where('id', '>=', $startId)
            ->where('durum', 'published')
            ->orderBy('id', 'asc')
            ->take(20)
            ->get();

        if ($stories->count() === 0) {
            return response()->json(['status' => 'error', 'message' => 'No stories found to compile.']);
        }

        // Chunk into groups of 5
        $chunks = $stories->chunk(5);
        $totalParts = $chunks->count();
        
        // Prepare IDs for each chunk to send back to client
        $chunkData = [];
        foreach($chunks as $index => $chunk) {
            $chunkData[] = [
                'part' => $index + 1,
                'story_ids' => $chunk->pluck('id')->toArray()
            ];
        }

        return response()->json([
            'status' => 'success',
            'volume' => $volume,
            'total_parts' => $totalParts,
            'chunks' => $chunkData,
            'start_id' => $stories->first()->id,
            'end_id' => $stories->last()->id
        ]);
    }

    // Step 2: Process a Single Chunk
    public function processChunk(Request $request)
    {
        set_time_limit(120);
        $storyIds = $request->input('story_ids');
        $volume = $request->input('volume');
        $part = $request->input('part');
        $totalParts = $request->input('total_parts');

        $stories = Story::whereIn('id', $storyIds)->orderBy('id', 'asc')->get();
        if ($stories->isEmpty()) {
             return response()->json(['status' => 'error', 'message' => 'Stories not found for this chunk.']);
        }

        // Prepare Text
        $chunkText = "";
        foreach ($stories as $story) {
            $text = strip_tags($story->metin);
            $chunkText .= "### BÖLÜM: {$story->baslik} (ID: {$story->id})\n{$text}\n\n";
        }

        try {
            // 1. Generate Illustration
            $partTitle = $stories->first()->baslik;
            $imgPrompt = "Anime style illustration for cyberpunk story chapter: $partTitle. Action scene or atmospheric city shot, cel shaded, high quality, no text.";
            $remoteImg = $this->aiService->generateImage($imgPrompt);
            $localImgPath = "ebooks/vol_{$volume}_part_{$part}_" . time() . ".jpg";
            $localImgUrl = $this->aiService->downloadImage($remoteImg, $localImgPath);

            // 2. Compass Text
            $partialHtml = $this->aiService->compileAnthology($chunkText, $volume, $part, $totalParts);

            // 3. Format Output
            $finalPartHtml = "<div class='volume-part' id='part-{$part}'>";
            $finalPartHtml .= "<div class='part-illustration' style='text-align:center; margin-bottom:2rem;'><img src='/" . $localImgPath . "' style='max-width:100%; border-radius:4px; border:1px solid #333;' alt='Chapter Art'></div>";
            $finalPartHtml .= $partialHtml;
            $finalPartHtml .= "</div><hr class='part-divider'>";

            // Extract Title from First Part (if Applicable)
            $extractedTitle = null;
            if ($part == 1) {
                 preg_match('/<h1>(.*?)<\/h1>/s', $partialHtml, $matches);
                 $extractedTitle = $matches[1] ?? null;
            }

            return response()->json([
                'status' => 'success',
                'html' => $finalPartHtml,
                'extracted_title' => $extractedTitle
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Step 3: Finalize and Save
    public function finalize(Request $request)
    {
         set_time_limit(120);
         $volume = $request->input('volume');
         $fullHtml = $request->input('full_html');
         $startId = $request->input('start_id');
         $endId = $request->input('end_id');
         $suggestedTitle = $request->input('suggested_title') ?? "Neo-Pera Chronicles Vol $volume";

         $cleanTitle = strip_tags($suggestedTitle);
         $slug = Str::slug($cleanTitle) . "-vol-$volume";

         try {
            // Generate Cover
            $coverPrompt = "Anime Style Book Cover for Cyberpunk Novel named '$cleanTitle'. Studio Ghibli meets Akira, detailed line art, cel shaded, no text, cinematic composition, 8k";
            $remoteCover = $this->aiService->generateImage($coverPrompt);
            $localPath = "ebooks/cover_vol_{$volume}_" . time() . ".jpg";
            $coverUrl = $this->aiService->downloadImage($remoteCover, $localPath);

            // Save DB
            $ebook = EBook::create([
                'title' => $cleanTitle,
                'slug' => $slug,
                'volume_number' => $volume,
                'content' => $fullHtml,
                'start_story_id' => $startId,
                'end_story_id' => $endId,
                'cover_image_url' => $coverUrl,
                'is_published' => true
            ]);

            return response()->json([
                'status' => 'success',
                'redirect_url' => route('ebooks.show', $ebook->slug)
            ]);

         } catch (\Exception $e) {
             return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
         }
    }
}
