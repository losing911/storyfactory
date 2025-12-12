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
        $this->apiKey = config('services.gemini.key');
    }

    public function generateFullStory(string $topic = null): array
    {
        // Fetch Random Lore
        $city = \App\Models\LoreEntry::where('type', 'city')->inRandomOrder()->first();
        $char = \App\Models\LoreEntry::where('type', 'character')->inRandomOrder()->first();
        $faction = \App\Models\LoreEntry::where('type', 'faction')->inRandomOrder()->first();

        // Build Lore Context
        $loreContext = "";
        $visualConstraints = [];

        if ($city) {
            $loreContext .= "ŞEHİR: {$city->title} ({$city->description})\n";
            if($city->visual_prompt) $visualConstraints[] = "City Style: " . $city->visual_prompt;
        }
        if ($char) {
            $loreContext .= "ANA KARAKTER: {$char->title} ({$char->description})\n";
            if($char->visual_prompt) $visualConstraints[] = "Character Appearance ({$char->title}): " . $char->visual_prompt;
        }
        if ($faction) {
            $loreContext .= "ÇETE/FAKSİYON: {$faction->title} ({$faction->description})\n";
            if($faction->visual_prompt) $visualConstraints[] = "Faction Integrity: " . $faction->visual_prompt;
        }

        $prompt = "Aşağıdaki özelliklere sahip bir Cyberpunk ÇİZGİ ROMAN (Comic Book) hikayesi oluştur. Çıktı SADECE JSON formatında olmalı ve dil KESİNLİKLE TÜRKÇE olmalı:\n\n";
        $prompt .= "Konu: " . ($topic ?? 'Rastgele bir Cyberpunk teması') . "\n";
        $prompt .= "Konu: " . ($topic ?? 'Rastgele bir Cyberpunk teması') . "\n";
        $prompt .= "--- EVREN BİLGİSİ (LORE) ---\n" . $loreContext . "----------------------------\n";
        $prompt .= "Stil: Cyberpunk 2077 / CD Projekt Red tarzı, fotogerçekçi, neon ışıklar, yüksek teknoloji, Night City atmosferi.\n";
        $prompt .= "ÖNEMLİ KURAL 1: Hikaye dili %100 TÜRKÇE olmalı.\n";
        $prompt .= "ÖNEMLİ KURAL 2: Başlıkta ve hikayede 'Neon' kelimesini ÇOK AZ kullan veya HİÇ KULLANMA. Teknoloji ve çürümüşlüğü vurgula, ışıkları değil.\n";
        $prompt .= "ÖNEMLİ KURAL 3: EVREN BİLGİSİ'ndeki Şehir, Karakter ve Faksiyonu MUTLAKA kullan.\n";
        
        $prompt .= "ÖNEMLİ KURAL 4 (GÖRSEL DİNAMİZM): Karakterleri asla 'sabit dururken' veya 'poz verirken' tarif etme. Sahneye göre şu varyasyonlardan birini MUTLAKA kullan:\n";
        $prompt .= "  - 'Action Pose': Karakter hareket halinde, koşuyor veya zıplıyor.\n";
        $prompt .= "  - 'Combat-Ready Version': Karakter dövüş pozisyonunda, silahı çekili, çatışmanın ortasında.\n";
        $prompt .= "  - 'Dramatic Scene Version': (Eski adı Stage) Karakter çok önemli, duygusal veya gergin bir anın içinde. Sinematik ışık, odaklanmış duruş. (Konser değil!)\n";
        $prompt .= "  - 'Hücre-34 Uniform Version': Eğer karakter Hücre-34 üyesiyse, bu formayı giydiğini belirt.\n";

        if(!empty($visualConstraints)) {
            $prompt .= "ÖNEMLİ KURAL 5 (GÖRSEL TUTARLILIK): img_prompt alanlarında şu görsel özellikleri KORU: " . implode(", ", $visualConstraints) . "\n";
        }
        $prompt .= "Yapı Gereksinimleri (ÇOK ÖNEMLİ):\n";
        $prompt .= "1. 'scenes' dizisi içinde EN AZ 6, EN FAZLA 10 sahne oluştur. Hikaye UZUN ve DETAYLI olmalı.\n";
        $prompt .= "2. Hikaye tam bir sonuca ulaşmalı (Giriş, Gelişme, Sonuç). Asla yarım kalmamalı.\n";
        $prompt .= "3. Her sahne en az 100-150 kelimeden oluşmalı, toplam hikaye 1000 kelimeyi geçmeli.\n";
        $prompt .= "4. Ana Başlık (baslik) belirle. (İçinde Neon geçmesin)\n";
        $prompt .= "5. Karakter: Ana karakterin kısa profili.\n";
        $prompt .= "6. Mod (mood): Hikayenin atmosferine uygun tek bir kelime seç: 'action', 'mystery', 'melancholy', 'high-tech', 'corruption'.\n";
        $prompt .= "7. SEO & Sosyal Medya alanlarını doldur.\n\n";
        $prompt .= "Görsel Prompt Kuralları:\n";
        $prompt .= "- Promptlar İNGİLİZCE olmalı.\n";
        $prompt .= "- Stil belirteçleri ekle: 'cyberpunk 2077 style, photorealistic, ray tracing, unreal engine 5, detailed textures, cinematic lighting'.\n";
        $prompt .= "- Konuşma balonu veya yazı İÇERMEMELİ ('no text, no speech bubbles').\n\n";
        $prompt .= "JSON Şeması:\n";
        $prompt .= "{\n";
        $prompt .= "  \"baslik\": \"...\",\n";
        $prompt .= "  \"scenes\": [\n";
        $prompt .= "    { \"text\": \"Sahne 1 metni (TÜRKÇE)...\", \"img_prompt\": \"Visual prompt (ENGLISH)...\" }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"karakter\": \"...\",\n";
        $prompt .= "  \"mood\": \"...\",\n";
        $prompt .= "  \"meta_baslik\": \"...\",\n";
        $prompt .= "  \"meta_aciklama\": \"...\",\n";
        $prompt .= "  \"etiketler\": [\"tag1\"],\n";
        $prompt .= "  \"sosyal_ozet\": \"...\",\n";
        $prompt .= "  \"new_lore\": [\n";
        $prompt .= "     { \"title\": \"İsim\", \"type\": \"character|faction|location\", \"description\": \"Kısa açıklama\", \"visual_prompt\": \"Görsel tarifi (English)\", \"is_new_invention\": true }\n";
        $prompt .= "  ]\n";
        $prompt .= "}";
        $prompt .= "\nÖNEMLİ: Eğer hikayede YENİ ve ÖNEMLİ bir karakter, mekan veya çete uydurduysan, 'new_lore' listesine ekle. Yoksa boş dizi bırak.\n";
        
        try {
            // Priority 1: Google Gemini
            return $this->generateWithGemini($prompt);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Gemini Failed: " . $e->getMessage() . ". Trying OpenRouter (DeepSeek)...");
            
            // Priority 2: OpenRouter (DeepSeek Free)
            return $this->generateWithOpenRouter($prompt, 'nex-agi/deepseek-v3.1-nex-n1:free');
        }
    }

    protected function generateWithGemini($prompt)
    {
         if (!$this->apiKey) {
            throw new \Exception('GEMINI_API_KEY is missing.');
        }

        // Increased to 120s for Gemini
        $response = Http::timeout(120)->post($this->baseUrl . '?key=' . $this->apiKey, [
            'contents' => [['parts' => [['text' => $prompt]]]]
        ]);

        if ($response->successful()) {
            $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            return $this->cleanAndDecodeJson($text);
        }

        throw new \Exception('Gemini API Error: ' . $response->body());
    }

    protected function generateWithOpenRouter($prompt, $model)
    {
        $openRouterKey = config('services.openrouter.key');
        if (!$openRouterKey) {
            throw new \Exception('OPENROUTER_API_KEY is missing.');
        }

        // Increased to 300s (5 mins) for DeepSeek/OpenRouter
        $response = Http::timeout(300)->withHeaders([
            'Authorization' => 'Bearer ' . $openRouterKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
             'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a creative JSON generator. You MUST output JSON only. You MUST write all story text in TURKISH language.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'response_format' => ['type' => 'json_object'] // Force JSON if supported
        ]);

        if ($response->successful()) {
            $text = $response->json()['choices'][0]['message']['content'] ?? '{}';
            return $this->cleanAndDecodeJson($text);
        }

        throw new \Exception("OpenRouter ($model) Error: " . $response->body());
    }

    protected function cleanAndDecodeJson($text)
    {
        $text = str_replace(['```json', '```'], '', $text);
        // Remove any text before the first '{' and after the last '}'
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $text = $matches[0];
        }
        
        $data = json_decode($text, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON syntax.');
        }

        // Critical Check: Ensure 'scenes' exists, otherwise trigger fallback
        if (!isset($data['scenes']) || !is_array($data['scenes'])) {
            throw new \Exception('JSON missing "scenes" key. Structure invalid.');
        }
        
        // Pass constraints to the controller via a special key (not used by AI directly but by our app)
        if(isset($visualConstraints) && !empty($visualConstraints)) {
            $data['meta_visual_prompts'] = implode(", ", $visualConstraints);
        }

        return $data;
    }

    public function generateImage(string $prompt, string $visualPrompt = null, string $refImageUrl = null): string
    {
        // Use Pollinations.ai with FLUX model (State of the Art)
        // Updated Style: Cyberpunk 2077 / Photorealistic
        $style = ", cyberpunk 2077 style, night city, photorealistic, ray tracing, unreal engine 5 render, cinematic lighting, neon lights, high tech low life, highly detailed, 8k, uhd, masterpiece, sharp focus";
        
        // 1. Inject Visual Consistency Prompt
        if ($visualPrompt) {
            $prompt .= ", " . $visualPrompt;
        }

        $encodedPrompt = urlencode($prompt . $style);
        
        // 2. Inject Reference Image (Experimental Support in Pollinations/Flux)
        // If the model supports img2img via URL, we append it. For now, Pollinations uses strict text-to-image mostly.
        // However, we can try appending the image URL to the seed or separate param if supported.
        // Current Strategy: Strong Prompting (Visual Prompt) is safer.
        // Future: If local Stable Diffusion, we would pass init_image.
        
        // Added '&model=flux' for better quality
        // Added '&enhance=true' (Pollinations feature)
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

    /**
     * Translate Story Content
     */
    public function translateContent(string $title, string $content, string $summary, string $targetLang = 'English'): array
    {
        // 1. Extract TEXT segments from HTML to avoid confusing the AI with large HTML structures
        // Match content inside <p>...</p> tags
        preg_match_all('/<p>(.*?)<\/p>/s', $content, $matches);
        $paragraphs = $matches[1] ?? [];
        
        // If no paragraphs found, maybe it's raw text? Use whole content.
        $segments = !empty($paragraphs) ? $paragraphs : [$content];
        
        // Prepare simple JSON structure for translation
        $payload = [
            'title' => $title,
            'summary' => $summary,
            'segments' => $segments
        ];
        
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $prompt = "You are a professional translator. Translate the JSON values from Turkish to {$targetLang}.\n";
        $prompt .= "RULES:\n";
        $prompt .= "1. Output MUST be valid JSON matching the input structure.\n";
        $prompt .= "2. Translate 'title', 'summary', and all items in 'segments' array.\n";
        $prompt .= "3. Do NOT change the number of segments.\n";
        $prompt .= "4. Strictly English output.\n";
        $prompt .= "\nInput JSON:\n" . $jsonPayload;

        try {
            $response = $this->generateWithGemini($prompt);
            
            // Reconstruct HTML if we had paragraphs
            $translatedContent = $content;
            if (!empty($paragraphs) && isset($response['segments']) && is_array($response['segments'])) {
                foreach ($paragraphs as $index => $originalPara) {
                    if (isset($response['segments'][$index])) {
                        // Replace the original paragraph content with translated one
                        // We use strict replacement to avoid replacing unintended parts
                        $translatedContent = str_replace($originalPara, $response['segments'][$index], $translatedContent);
                    }
                }
            } elseif(!empty($response['segments'][0])) {
                 // Fallback for raw text
                 $translatedContent = $response['segments'][0];
            }

            return [
                'title' => $response['title'] ?? $title,
                'content' => $translatedContent,
                'summary' => $response['summary'] ?? $summary
            ];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Gemini Translation Failed: " . $e->getMessage());
            
             // Fallback to OpenRouter with same chunk logic
            try {
                 $response = $this->generateWithOpenRouter($prompt, 'nex-agi/deepseek-v3.1-nex-n1:free');
                 // Repeat reconstruction logic (Refactor into helper if needed, but duplication is safer for now)
                 $translatedContent = $content;
                 if (!empty($paragraphs) && isset($response['segments']) && is_array($response['segments'])) {
                    foreach ($paragraphs as $index => $originalPara) {
                        if (isset($response['segments'][$index])) {
                            $translatedContent = str_replace($originalPara, $response['segments'][$index], $translatedContent);
                        }
                    }
                }
                return [
                    'title' => $response['title'] ?? $title,
                    'content' => $translatedContent,
                    'summary' => $response['summary'] ?? $summary
                ];

            } catch (\Exception $e2) {
                \Illuminate\Support\Facades\Log::error("All Translation Providers Failed: " . $e2->getMessage());
                return [
                    'title' => $title . " [FAIL]", 
                    'content' => $content,
                    'summary' => $summary
                ];
            }
        }
    }

    protected function getMockData(): array
    {
        return []; // Mock data deprecated
    }
}
