<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        // Priority 1: Stories pending Video (if we implement video flow)
        // Priority 2: Stories pending Images (Visuals)
        
        // Find a story that needs images
        // We assume 'pending_visuals' status means images are needed
        $story = Story::where('durum', 'pending_visuals')->first();

        if ($story) {
             // Decode the prompts array
             $prompts = json_decode($story->gorsel_prompt, true); // Note: Column is gorsel_prompt in code, img_prompt in JSON
             $coverPrompt = is_array($prompts) ? ($prompts[0] ?? $story->title) : $story->title;

            return response()->json([
                'id' => $story->id,
                'type' => 'image_generation',
                'prompt' => $coverPrompt,
                'style_preset' => 'flux_schnell' 
            ]);
        }

        // Add Video Logic here later if needed
        
        return response()->json(null); // No jobs
    }

    public function completeJob(Request $request)
    {
        $this->checkAuth($request);
        
        $validated = $request->validate([
            'job_id' => 'required',
            'type' => 'required',
            'file_content' => 'required', // Base64 encoded file
            'filename' => 'required' 
        ]);

        $story = Story::find($validated['job_id']);
        if (!$story) return response()->json(['error' => 'Story not found'], 404);

        $imageData = base64_decode($validated['file_content']);
        $path = 'stories/images/' . $validated['filename'];
        
        // Save to public disk
        Storage::disk('public')->put($path, $imageData);
        
        // Update Story
        if ($validated['type'] == 'image_generation') {
            $story->gorsel_url = '/storage/' . $path;
            $story->durum = 'published'; // Publish immediately after image is ready
            $story->save();
        }

        return response()->json(['status' => 'success', 'url' => $story->gorsel_url]);
    }
}
