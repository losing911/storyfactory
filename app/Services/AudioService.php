<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AudioService
{
    protected $apiKey;
    protected $baseUrl = 'https://texttospeech.googleapis.com/v1/text:synthesize';

    public function __construct()
    {
        $this->apiKey = config('services.google_tts.key') ?? env('GOOGLE_TTS_KEY');
    }

    /**
     * Generate Speech from Text (Google TTS)
     * Returns: Public URL of the generated MP3
     */
    public function generateSpeech(string $text, string $slug): string
    {
        if (!$this->apiKey) {
            // Fallback to browser TTS if no key
            // We return empty string, view handles fallback
            return ""; 
        }

        // 1. Text Cleanup (Limit length)
        $text = strip_tags($text);
        $text = mb_substr($text, 0, 4500); // Google Limit is ~5000 chars

        // 2. Cache Check (Hash of text + Voice Config)
        $voiceName = 'tr-TR-Wavenet-B'; // High quality Wavenet Male
        $configHash = 'v2-rate1.0-pitch0'; // Versioning for settings
        $hash = md5($text . $voiceName . $configHash); 
        
        $fileName = "audio/tts/{$slug}_{$hash}.mp3";
        $fullPath = public_path($fileName);

        if (file_exists($fullPath)) {
            return asset($fileName);
        }

        try {
            $response = Http::post($this->baseUrl . '?key=' . $this->apiKey, [
                'input' => ['text' => $text],
                'voice' => [
                    'languageCode' => 'tr-TR',
                    'name' => $voiceName,
                    'ssmlGender' => 'MALE'
                ],
                'audioConfig' => [
                    'audioEncoding' => 'MP3',
                    'speakingRate' => 1.00, // Normal speed for clarity
                    'pitch' => 0.00         // Natural pitch
                ]
            ]);

            if ($response->successful()) {
                $audioContent = base64_decode($response->json()['audioContent']);
                
                // Ensure dir exists
                if (!file_exists(public_path('audio/tts'))) {
                    mkdir(public_path('audio/tts'), 0755, true);
                }

                file_put_contents($fullPath, $audioContent);
                return asset($fileName);
            }
            
            Log::error("Google TTS Error: " . $response->body());
            return "";

        } catch (\Exception $e) {
            Log::error("Audio Service Failure: " . $e->getMessage());
            return "";
        }
    }
}
