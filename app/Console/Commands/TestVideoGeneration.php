<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestVideoGeneration extends Command
{
    protected $signature = 'story:test-video {prompt : The prompt for the video} {--model=Veo 3.1 Fast : The model to use (e.g. "Veo 3.1 Fast", "Seedance Lite", "Seedance Pro-Fast")}';
    protected $description = 'Test video generation using Pollinations.ai with specific models';

    public function handle()
    {
        $prompt = $this->argument('prompt');
        $model = $this->option('model');

        $this->info("Attempting to generate video with prompt: '$prompt' using model: '$model'");

        // Encode Prompt and Model
        $encodedPrompt = urlencode($prompt);
        $encodedModel = urlencode($model);
        // Add random seed
        $seed = rand(1, 99999);
        
        // Construct URL
        $url = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=1280&height=720&model={$encodedModel}&seed={$seed}&nologo=true";

        $this->info("Requesting URL: $url");
        $this->info("Timeout set to 180 seconds...");

        try {
            $response = Http::timeout(180)->get($url);

            if ($response->successful()) {
                $contentType = $response->header('Content-Type');
                $size = strlen($response->body());
                
                $this->info("Success! Status: " . $response->status());
                $this->info("Content-Type: " . $contentType);
                $this->info("Size: " . round($size / 1024, 2) . " KB");

                // Determine extension
                $ext = 'mp4';
                if (strpos($contentType, 'image') !== false) {
                    $this->warn("Warning: Returned content type is IMAGE, not VIDEO. Model might not support video via this endpoint.");
                    $ext = 'jpg';
                }

                $filename = "test_video_{$seed}.{$ext}";
                Storage::disk('public')->put($filename, $response->body());
                
                $this->info("Saved to: storage/app/public/$filename");
                $this->info("Public URL: " . asset("storage/$filename"));

            } else {
                $this->error("Failed! Status: " . $response->status());
                $this->error("Body: " . substr($response->body(), 0, 500));
            }

        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
        }
    }
}
