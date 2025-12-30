<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestGoogleVideo extends Command
{
    protected $signature = 'story:test-google-video {prompt : The prompt for the video}';
    protected $description = 'Test video generation using Google Veo via Gemini API Key';

    public function handle()
    {
        $prompt = $this->argument('prompt');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            $this->error("GEMINI_API_KEY not found in .env");
            return;
        }

        $this->info("Attempting to generate video with prompt: '$prompt' using Google Veo...");
        $this->info("Using key: " . substr($apiKey, 0, 5) . "...");

        // Endpoint for Veo (Experimental/Beta)
        // Common endpoint pattern for generative tasks
        $url = "https://generativelanguage.googleapis.com/v1beta/models/veo-2.0-generate-001:predict?key={$apiKey}";
        
        $this->info("Target URL: $url");

        $payload = [
            "instances" => [
                [
                    "prompt" => $prompt
                ]
            ],
            "parameters" => [
                "sampleCount" => 1,
                "aspectRatio" => "16:9",
                "durationSeconds" => 6 // Default usually around 6-8s
            ]
        ];

        try {
            $response = Http::timeout(120)->post($url, $payload);

            if ($response->successful()) {
                $this->info("API Call Successful!");
                $data = $response->json();
                
                // Inspect structure
                // Usually returns 'predictions' with bytes or a video URI
                if (isset($data['predictions'][0]['bytesBase64Encoded'])) {
                    $videoData = base64_decode($data['predictions'][0]['bytesBase64Encoded']);
                    $filename = "google_veo_" . time() . ".mp4";
                    Storage::disk('public')->put($filename, $videoData);
                    $this->info("Video Saved: storage/app/public/$filename");
                    $this->info("URL: " . asset("storage/$filename"));
                } elseif (isset($data['predictions'][0]['videoUri'])) {
                    $this->info("Video URI received: " . $data['predictions'][0]['videoUri']);
                    // Download it?
                    $this->warn("Model returned a URI, not bytes. You might need to download it manually.");
                } else {
                    $this->warn("Unexpected response structure:");
                    $this->line(json_encode($data, JSON_PRETTY_PRINT));
                }

            } else {
                $this->error("Failed! Status: " . $response->status());
                $this->error("Body: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
        }
    }
}
