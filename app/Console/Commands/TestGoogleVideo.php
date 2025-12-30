<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestGoogleVideo extends Command
{
    protected $signature = 'story:test-google-video {prompt : The prompt for the video} {--model=veo-3.1-fast-generate-001 : The model ID} {--list : List available models}';
    protected $description = 'Test video generation using Google Veo via Gemini API Key';

    public function handle()
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            $this->error("GEMINI_API_KEY not found in .env");
            return;
        }

        // Handle List Option
        if ($this->option('list')) {
            $this->info("Fetching available models from Google API...");
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
            
            try {
                $response = Http::get($url);
                if ($response->successful()) {
                    $models = $response->json()['models'] ?? [];
                    $found = 0;
                    foreach ($models as $m) {
                        // Filter for relevant models
                        if (str_contains($m['name'], 'veo') || str_contains($m['name'], 'video') || str_contains($m['name'], 'imagen')) {
                            $this->line(" - <info>" . str_replace("models/", "", $m['name']) . "</info> | " . ($m['displayName'] ?? ''));
                            $found++;
                        }
                    }
                    if ($found == 0) {
                        $this->warn("No specific 'veo' or 'video' models found in the public list.");
                        $this->line("Printing top 5 models to verify connection:");
                        foreach (array_slice($models, 0, 5) as $m) {
                            $this->line(" - " . $m['name']);
                        }
                    }
                } else {
                    $this->error("Failed to list models: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("Connection Error: " . $e->getMessage());
            }
            return;
        }

        $prompt = $this->argument('prompt');
        $model = $this->option('model');

        $this->info("Attempting to generate video with prompt: '$prompt' using model: '$model'");
        $this->info("Using key: " . substr($apiKey, 0, 5) . "...");

        // Endpoint for Veo
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:predict?key={$apiKey}";
        
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
                "durationSeconds" => 6
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
