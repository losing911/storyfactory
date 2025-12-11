<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $apiKey;
    // protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    // protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent';
    // Modeller listesi (Eğer biri calismazsa diğerini deneyin):
    // protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';
    // protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent';
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent'; // Script tarafından doğrulanan model

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function generateFullStory(string $topic = null): array
    {
        $prompt = "Aşağıdaki özelliklere sahip bir Cyberpunk ÇİZGİ ROMAN (Comic Book) hikayesi oluştur. Çıktı SADECE JSON formatında olmalı:\n\n";
        $prompt .= "Konu: " . ($topic ?? 'Rastgele bir Cyberpunk teması') . "\n";
        $prompt .= "Stil: Frank Miller / Moebius tarzı, karanlık, yağmurlu, distopik, ciddi ve ağır atmosfer.\n";
        $prompt .= "ÖNEMLİ KURAL: Başlıkta ve hikayede 'Neon' kelimesini ÇOK AZ kullan veya HİÇ KULLANMA. Teknoloji ve çürümüşlüğü vurgula, ışıkları değil.\n";
        $prompt .= "Yapı Gereksinimleri (ÇOK ÖNEMLİ):\n";
        $prompt .= "1. 'scenes' dizisi içinde EN AZ 6, EN FAZLA 10 sahne oluştur. Hikaye UZUN ve DETAYLI olmalı.\n";
        $prompt .= "2. Hikaye tam bir sonuca ulaşmalı (Giriş, Gelişme, Sonuç). Asla yarım kalmamalı.\n";
        $prompt .= "3. Her sahne en az 100-150 kelimeden oluşmalı, toplam hikaye 1000 kelimeyi geçmeli.\n";
        $prompt .= "4. Ana Başlık (baslik) belirle. (İçinde Neon geçmesin)\n";
        $prompt .= "5. Karakter: Ana karakterin kısa profili.\n";
        $prompt .= "6. SEO & Sosyal Medya alanlarını doldur.\n\n";
        $prompt .= "Görsel Prompt Kuralları:\n";
        $prompt .= "- Promptlar İNGİLİZCE olmalı.\n";
        $prompt .= "- Stil belirteçleri ekle: 'comic book style, thick lines, atmospheric lighting, cel shaded, masterpiece, 8k'.\n";
        $prompt .= "- Konuşma balonu veya yazı İÇERMEMELİ ('no text, no speech bubbles').\n\n";
        
        $prompt .= "JSON Şeması:\n";
        $prompt .= "{\n";
        $prompt .= "  \"baslik\": \"...\",\n";
        $prompt .= "  \"scenes\": [\n";
        $prompt .= "    { \"text\": \"Sahne 1 metni...\", \"img_prompt\": \"Scene 1 visual description...\" },\n";
        $prompt .= "    { \"text\": \"Sahne 2 metni...\", \"img_prompt\": \"Scene 2 visual description...\" }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"karakter\": \"...\",\n";
        $prompt .= "  \"meta_baslik\": \"...\",\n";
        $prompt .= "  \"meta_aciklama\": \"...\",\n";
        $prompt .= "  \"etiketler\": [\"tag1\"],\n";
        $prompt .= "  \"sosyal_ozet\": \"...\"\n";
        $prompt .= "}";

        try {
            // Explicit check for API Key
            if (!$this->apiKey) {
                throw new \Exception('GEMINI_API_KEY is missing in .env file.');
            }

            // 1 Hour timeout for extreme cases
            $response = Http::timeout(3600)->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                // Clean markdown code blocks if present
                $text = str_replace(['```json', '```'], '', $text);
                
                return json_decode($text, true) ?? $this->getMockData();
            }

            // Log::error('Gemini API Error: ' . $response->body()); // Log facade usage corrected elsewhere
            throw new \Exception('Gemini API returned error: ' . $response->body());

        } catch (\Exception $e) {
            // Log::error('AIService Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generateImage(string $prompt): string
    {
        // Use Pollinations.ai with FLUX model (State of the Art)
        // Add style keywords for high-quality Cyberpunk Comic look. Removed 'neon' emphasis.
        $style = ", cyberpunk comic book style, gritty, noir atmosphere, frank miller aesthetic, cel shaded, bold thick lines, atmospheric lighting, muted colors, cinematic composition, highly detailed, masterpiece, 8k, uhd, no text, no speech bubbles";
        $encodedPrompt = urlencode($prompt . $style);
        
        // Added '&model=flux' for better quality
        return "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=1280&height=720&nologo=true&model=flux&seed=" . rand(1, 99999);
    }

    public function downloadImage(string $url, string $path): string
    {
        // Use Laravel HTTP Client with Retry logic (3 times, 2s delay)
        $response = Http::withoutVerifying()
            ->timeout(3600)
            ->retry(3, 2000) // Retry on 502, 500, etc.
            ->get($url);
        
        if ($response->failed()) {
             throw new \Exception("Failed to download image from $url. Status: " . $response->status());
        }
        
        $contents = $response->body();
        
        // Ensure directory exists
        $directory = dirname(storage_path('app/public/' . $path));
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(storage_path('app/public/' . $path), $contents);
        
        return '/storage/' . $path;
    }

    protected function getMockData(): array
    {
        return []; // Mock data deprecated
    }
}
