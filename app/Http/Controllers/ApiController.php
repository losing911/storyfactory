<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    private function checkAuth(Request $request) {
        $token = $request->header('Authorization');
        // Simple Bearer token check
        if ($token !== 'Bearer ' . env('WORKER_AUTH_TOKEN', 'anxipunk_secret_worker_key_2025')) {
            abort(401, 'Unauthorized');
        }
    }

    public function getPendingJobs(Request $request)
    {
        $this->checkAuth($request);

        // Priority: Stories pending Visuals OR Drafts (Taslak)
        // Loop through pending stories to find one that ACTUALLY needs work
        // This prevents "Zombie Stories" (Status: pending, but no placeholders) from blocking the queue
        // Priority: Stories pending Visuals OR Drafts (Taslak)
        // Loop through pending stories to find one that ACTUALLY needs work
        // This prevents "Zombie Stories" (Status: pending, but no placeholders) from blocking the queue
        // Added Case-Insensitive variants just in case
        $stories = Story::whereIn('durum', ['pending_visuals', 'taslak', 'draft', 'Taslak', 'Draft'])->get();
        $placeholderSign = "https://placehold.co/1280x720/1f2937/00ff00";

        Log::info("Worker Polling: Found " . $stories->count() . " potential stories.");

        foreach ($stories as $story) {
            
            if (strpos($story->metin, $placeholderSign) !== false) {
                // FOUND ONE!
                
                // Parse HTML to find the first image with this src
                preg_match('/src=[\'"]' . preg_quote($placeholderSign, '/') . '.*?[\'"].*?alt=[\'"]Scene (\d+)[\'"]/s', $story->metin, $matches);
                
                if (isset($matches[1])) {
                    $index = intval($matches[1]);
                    $prompts = json_decode($story->gorsel_prompt, true);
                    
                    if (isset($prompts[$index])) {
                        Log::info("Job Dispatched: Story {$story->id} Scene {$index}");
                        return response()->json([
                            'id' => $story->id,
                            'type' => 'image_generation',
                            'scene_index' => $index,
                            'prompt' => $prompts[$index],
                            'style_preset' => 'turbo' 
                        ]);
                    } else {
                        Log::warning("Skipping Story {$story->id}: Prompt missing for index {$index}");
                    }
                }
            } else {
                // No placeholders found? 
                Log::info("Skipping Story {$story->id}: No placeholders found in text.");
                // If it was 'pending_visuals', it's a Zombie -> Auto Publish.
                // If it was 'taslak' or 'draft', it's just a manual draft -> Leave it alone.
                 if ($story->durum === 'pending_visuals') {
                     $story->durum = 'published';
                     $story->save();
                     Log::info("Auto-Published Zombie Story ID: {$story->id}");
                 }
            }
        }

        return response()->json(null); // No jobs
    }

    public function completeJob(Request $request)
    {
        $this->checkAuth($request);
        
        $validated = $request->validate([
            'job_id' => 'required',
            'type' => 'required',
            'file_content' => 'required',
            'filename' => 'required',
            'scene_index' => 'required|integer' // We need to know which scene we fixed
        ]);

        $story = Story::find($validated['job_id']);
        if (!$story) return response()->json(['error' => 'Story not found'], 404);

        $imageData = base64_decode($validated['file_content']);
        $path = 'stories/images/' . $validated['filename'];
        
        Storage::disk('public')->put($path, $imageData);
        $publicUrl = '/storage/' . $path;
        
        if ($validated['type'] == 'image_generation') {
            $index = $validated['scene_index'];
            
            // 1. If it's the first scene (Index 0), it's also the Cover
            if ($index === 0) {
                $story->gorsel_url = $publicUrl;
            }

            // 2. Replace the Placeholder in `metin` HTML
            // We look for the specific tag for Scene X
            $placeholderSign = "https://placehold.co/1280x720/1f2937/00ff00";
            // Regex matches the whole img tag that contains the placeholder AND alt='Scene $index'
            $pattern = '/<img[^>]+src=[\'"]' . preg_quote($placeholderSign, '/') . '.*?[\'"][^>]+alt=[\'"]Scene ' . $index . '[\'"][^>]*>/i';
            
            // Construct new clean IMG tag
            $newImgTag = "<img src='$publicUrl' alt='Scene $index' class='w-full rounded shadow-lg border-2 border-neon-blue/50 transition duration-500'>";
            
            $story->metin = preg_replace($pattern, $newImgTag, $story->metin);
            
            // 3. Check if any placeholders remain
            $isFinished = false;
            if (strpos($story->metin, $placeholderSign) === false) {
                // Double check to ensure we didn't miss any
                // ONLY auto-publish if it was in 'pending_visuals' mode. 
                // If it's a draft (taslak), keep it as draft for manual review.
                if ($story->durum === 'pending_visuals') {
                    $story->durum = 'published';
                    $story->save();
                }
                $isFinished = true;
                Log::info("Story {$story->id} fully visualized and published.");
            } else {
                // Still working...
                Log::info("Story {$story->id} scene $index updated. More pending.");
            }
            
            $story->save();
        }

        return response()->json([
            'status' => 'success', 
            'url' => $publicUrl,
            'story_finished' => $isFinished ?? false
        ]);
    }

    // Endpoint for Local Twitter Bot
    public function getLatestStory() {
        $story = Story::where('durum', 'published')->latest()->first();
        if(!$story) return response()->json(null, 404);

        return response()->json([
            'id' => $story->id,
            'title' => $story->baslik,
            'summary' => $story->sosyal_ozet ?? Str::limit(strip_tags($story->metin), 200),
            'url' => route('story.show', $story),
            'tags' => $story->etiketler ?? ['Cyberpunk', 'Hikaye'],
            'image_url' => asset($story->gorsel_url)
        ]);
    }

    // Public API: List Stories
    public function index()
    {
        $stories = Story::where('durum', 'published')
            ->select('id', 'baslik', 'slug', 'gorsel_url', 'yayin_tarihi', 'sosyal_ozet', 'mood', 'konu')
            ->latest()
            ->paginate(10);
            
        // Append full image URLs
        $stories->getCollection()->transform(function ($story) {
            $story->gorsel_url = $story->gorsel_url ? asset($story->gorsel_url) : null;
            return $story;
        });

        return response()->json($stories);
    }

    // Public API: Show Story
    public function show($id)
    {
        // Support ID or Slug
        $story = is_numeric($id) 
            ? Story::where('id', $id)->where('durum', 'published')->first()
            : Story::where('slug', $id)->where('durum', 'published')->first();

        if (!$story) return response()->json(['error' => 'Story not found'], 404);

        return response()->json([
            'id' => $story->id,
            'title' => $story->baslik,
            'slug' => $story->slug,
            'content' => $story->metin, // Sends full HTML
            'text_content' => strip_tags($story->metin), // Plain text for bots
            'summary' => $story->sosyal_ozet,
            'image_url' => $story->gorsel_url ? asset($story->gorsel_url) : null,
            'published_at' => $story->yayin_tarihi,
            'mood' => $story->mood,
            'topic' => $story->konu,
            'tags' => $story->etiketler,
            'url' => route('story.show', $story)
        ]);
    }

    // Public API: List Lore
    public function lore()
    {
        $lore = \App\Models\LoreEntry::where('is_active', true)
            ->select('title', 'slug', 'type', 'description', 'image_url')
            ->latest()
            ->get();
            
        $lore->transform(function ($item) {
             $item->image_url = $item->image_url ? asset($item->image_url) : null;
             return $item;
        });

        return response()->json($lore);
    }
}
