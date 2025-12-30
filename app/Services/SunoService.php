<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SunoService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // Example: https://suno-api-binding.vercel.app or a custom proxy
        $this->baseUrl = env('SUNO_API_URL', 'http://localhost:3000'); 
        $this->apiKey = env('SUNO_API_KEY');
    }

    /**
     * Generate music based on a prompt.
     * 
     * @param string $prompt Description of the music (e.g., "Dark cyberpunk synthwave, heavy bass, uptempo 140bpm")
     * @param boolean $instrumental Whether to generate instrumental only
     * @return string|null URL of the generated audio or null on failure
     */
    public function generateMusic(string $prompt, bool $instrumental = true)
    {
        if (!$this->baseUrl) {
            Log::warning("SunoService: SUNO_API_URL is not set.");
            return null;
        }

        try {
            Log::info("SunoService: Generating music for prompt: $prompt");

            // Hypothetical API Structure (based on common unofficials)
            // Example: POST /api/generate
            // Payload: { "prompt": "...", "make_instrumental": true, "wait_audio": true }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->timeout(120)->post("{$this->baseUrl}/api/generate", [
                'prompt' => $prompt,
                'make_instrumental' => $instrumental,
                'wait_audio' => true // Some APIs support waiting
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Parse response (Adjust based on actual API)
                // Assuming it returns a list of results
                if (!empty($data[0]['audio_url'])) {
                    return $data[0]['audio_url'];
                }
                
                // Fallback parsing
                if (!empty($data['audio_url'])) {
                    return $data['audio_url'];
                }

                Log::warning("SunoService: Unexpected response format: " . json_encode($data));
            } else {
                Log::error("SunoService: Request failed. Status: " . $response->status() . " Body: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("SunoService: Exception " . $e->getMessage());
        }

        return null;
    }
}
