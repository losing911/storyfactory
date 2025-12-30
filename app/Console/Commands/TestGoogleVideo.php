<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestGoogleVideo extends Command
{
    protected $signature = 'story:test-google-video {prompt : The prompt for the video} {--model=veo-3.1-fast-generate-preview : The model ID} {--list : List available models}';
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
        // Payload for Veo (Google AI Studio / Vertex AI style)
        // Note: 'predict' might be wrong, trying 'predictLongRunning' or 'generateContent'
        // Documentation suggests video generation is a long-running operation.
        
        $mode = 'predictLongRunning'; 
        // Some docs suggest: https://generativelanguage.googleapis.com/v1beta/models/...:predict
        // But user got "not supported for predict".
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:{$mode}?key={$apiKey}";
        
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
            $this->info("Sending LONG RUNNING request ($mode)...");
            $response = Http::timeout(60)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                // Check for operation name
                if (isset($data['name'])) {
                    $operationName = $data['name'];
                    $this->info("Operation started! ID: $operationName");
                    $this->info("Polling for completion...");
                    
                    // POLLING LOOP
                    $attempts = 0;
                    while ($attempts < 30) {
                        sleep(5);
                        $attempts++;
                        $pollUrl = "https://generativelanguage.googleapis.com/v1beta/{$operationName}?key={$apiKey}";
                        $pollResp = Http::get($pollUrl);
                        
                        if ($pollResp->successful()) {
                            $pollData = $pollResp->json();
                            if (isset($pollData['done']) && $pollData['done'] === true) {
                                if (isset($pollData['response']['predictions'][0]['videoUri'])) {
                                    $videoUri = $pollData['response']['predictions'][0]['videoUri'];
                                    $this->info("SUCCESS! Video generated.");
                                    $this->info("Video URI: $videoUri");
                                    $this->warn("Note: This URI might be short-lived. Download it manually if needed.");
                                    return;
                                } elseif (isset($pollData['error'])) {
                                    $this->error("Operation failed with error: " . json_encode($pollData['error']));
                                    return;
                                } else {
                                     // Check for 'result' field in some versions
                                     $this->info("Operation done. Full response:");
                                     $this->line(json_encode($pollData));
                                     return;
                                }
                            } else {
                                $this->info("... working ... (Attempt $attempts)");
                            }
                        } else {
                            $this->error("Polling failed: " . $pollResp->status());
                            break;
                        }
                    }
                    $this->error("Timeout waiting for video generation.");

                } else {
                     // Maybe it returned direct result?
                     $this->info("Response received (No Operation ID):");
                     $this->line(substr($response->body(), 0, 500));
                }

            } else {
                $this->error("Failed! Status: " . $response->status());
                $this->error("Body : " . $response->body());
                $this->warn("Try checking if 'Vertex AI API' is enabled in your Google Cloud Console project linked to this key.");
            }

        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
        }
    }
}
