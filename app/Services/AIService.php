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
            $loreContext .= "  -> DİKKAT: Bu karakterin rolü/mesleği ({$char->type}) SABİTTİR. Asla değiştirme. Örneğin bir Direnişçi ise, asla Ajan olamaz.\n";
            if($char->visual_prompt) $visualConstraints[] = "Character Appearance ({$char->title}): " . $char->visual_prompt;
        }
        if ($faction) {
            $loreContext .= "ÇETE/FAKSİYON: {$faction->title} ({$faction->description})\n";
            $loreContext .= "  -> DİKKAT: Bu grubun/çetenin sadakati ve amacı ({$faction->type}) SABİTTİR. Değiştirme.\n";
            if($faction->visual_prompt) $visualConstraints[] = "Faction Integrity: " . $faction->visual_prompt;
        }

        // Randomize Mood/Genre to avoid constant melancholy
        $moods = [
            'High Octane Action: Chase scenes, combat, adrenaline, fast-paced.',
            'Corporate Intrigue: Espionage, data theft, betrayal, white-collar crime.',
            'Tech Discovery: Finding a lost technology, AI awakening, cyber-archaeology.',
            'Neon Noir Mystery: Detective work, missing persons, solving a crime.',
            'Underground Culture: Rave parties, illegal racing, cyber-drug trade, art.',
            'Cyber-Revolution: Riots, hacking the system, overthrowing the elite, high stakes.'
        ];
        $selectedMood = $moods[array_rand($moods)];

        $prompt = "Aşağıdaki özelliklere sahip bir 'ANXIPUNK' (Anxiety + Cyberpunk) türünde, KARAKTER ODAKLI ve EDEBİ derinliği olan bir hikaye oluştur. Çıktı SADECE JSON formatında olmalı ve dil KESİNLİKLE TÜRKÇE olmalı:\n\n";
        $prompt .= "Konu: " . ($topic ?? "Odaklanılacak Tema: $selectedMood") . "\n";
        $prompt .= "--- EVREN BİLGİSİ (LORE) ---\n" . $loreContext . "--------------------------------------------------------\n";
        $prompt .= "ATMOSFER & STİL (ANXIPUNK): Cyberpunk 2077'nin arka sokakları. Ancak SADECE depresif değil; seçilen temaya ($selectedMood) uygun bir atmosfer yarat. 'High Tech, Low Life' prensibini koru.\n";
        $prompt .= "ÖNEMLİ KURAL 1: Hikaye dili %100 EDEBİ TÜRKÇE olmalı. Basit cümleler kurma, betimlemeleri zengin tut.\n";
        $prompt .= "ÖNEMLİ KURAL 2 (KLİŞELERİ YIK): 'Neon ışıkları altında', 'yağmur yağıyordu' gibi klasik girişleri yasakla. Okuyucuyu karakterin zihnine, o anki spesifik sorununa (açlık, borç, yalnızlık, glitch nöbeti vb.) odakla.\n";
        $prompt .= "ÖNEMLİ KURAL 3 (KARAKTER DERİNLİĞİ): Karakter sadece bir 'sınıf' (Hacker, Solo vb.) değildir. Onun korkuları, takıntıları, küçük zevkleri olmalı. Diyaloglar doğal ve sokak ağzına uygun olsun.\n";
        
        $prompt .= "\n=== NEO-PERA LORE KURALLARI (ÇOK ÖNEMLİ) ===\n";
        $prompt .= "KURAL NEO-1 - YASAKLI KLİŞELER (ASLA KULLANMA):\n";
        $prompt .= "  ❌ 'neon ışıklar', 'metal yığını', 'siberuzayda süzülmek', 'robot kollar'\n";
        $prompt .= "  ✅ Bunlar yerine: spesifik detaylar ver (örn: 'Tabela cızırtıyla yanıp sönen pembe fosforun altında...')\n";
        $prompt .= "  ❌ 'yapay zeka' -> ✅ 'sentetik zihin', 'veri ruhu'\n";
        $prompt .= "  ❌ 'matrix' -> ✅ 'veri ağı', 'nöral kafes'\n";
        $prompt .= "  ❌ 'hacker' -> ✅ 'netrunner', 'veri kazıyıcı'\n\n";
        
        $prompt .= "KURAL NEO-2 - DUYUSAL DERİNLİK (ZORUNLU):\n";
        $prompt .= "  Her hikayede EN AZ 2 FARKLI DUYU kullan:\n";
        $prompt .= "  🫁 KOKU: (Örn: yanık devre kartı, ucuz sentetik noodle, asit yağmuru sonrası toprak kokusu)\n";
        $prompt .= "  👂 SES: (Örn: fanların uğultusu, uzaktan gelen siren, mekanik öksürük)\n";
        $prompt .= "  👅 TAT/HİS: (Örn: ağızdaki metalik tat, ensedeki çip girişinin kaşınması)\n\n";
        
        $prompt .= "KURAL NEO-3 - İNSAN KUSURLARI (ZORUNLU):\n";
        $prompt .= "  Karakterler MÜKEMMM EL OLMAMALI:\n";
        $prompt .= "  - Kekelesinler, unutsunlar, cihazları bozulsun, yorgun olsunlar\n";
        $prompt .= "  - Örnekler: 'titreyen elleriyle', 'unutkan hafızası', 'arızalı nöral implantı', 'yorgunluktan gözleri kızarmış'\n\n";
        
        $prompt .= "KURAL NEO-4 - SEO & FORMATLAMA:\n";
        $prompt .= "  - Önemli terimleri (karakter isimleri, faksiyonlar, teknoloji) **kalın** yaz\n";
        $prompt .= "  - Kısa paragraflar kullan (3-4 cümle maks)\n";
        $prompt .= "  - Dümdüz metin bloğu değil, okumayı kolaylaştıracak yapı\n";
        $prompt .= "================================================\n\n";
        
        $prompt .= "ÖNEMLİ KURAL 4 (GÖRSEL DİNAMİZM): Karakterleri asla 'sabit dururken' tarif etme. Sahneye göre şu varyasyonlardan birini MUTLAKA kullan:\n";
        $prompt .= "  - 'Candid Shot': Karakter habersizce yakalanmış, doğal bir anın içinde (yemek yerken, tamir yaparken, düşünürken).\n";
        $prompt .= "  - 'Emotional Close-up': Yüz ifadesine ve gözlerdeki duyguya odaklan.\n";
        $prompt .= "  - 'Environmental Portrait': Karakterin yaşadığı dağınık, kirli ama detaylı mekanı göster.\n";

        if(!empty($visualConstraints)) {
            $prompt .= "ÖNEMLİ KURAL 5 (GÖRSEL TUTARLILIK): img_prompt alanlarında şu görsel özellikleri KORU: " . implode(", ", $visualConstraints) . "\n";
        }
        $prompt .= "Yapı Gereksinimleri (ÇOK ÖNEMLİ):\n";
        $prompt .= "1. 'scenes' dizisi içinde EN AZ 6, EN FAZLA 10 sahne oluştur. Hikaye UZUN ve DETAYLI olmalı.\n";
        $prompt .= "2. Hikaye tam bir sonuca ulaşmalı (Giriş, Gelişme, Sonuç). Asla yarım kalmamalı.\n";
        $prompt .= "3. Her sahne EN AZ 300 KELİME olmalı. Diyaloglar, iç sesler ve detaylı mekan tasvirleri ile sahneyi uzat. Acele etme.\n";
        $prompt .= "4. Ana Başlık (baslik) belirle. (İçinde Neon geçmesin)\n";
        $prompt .= "5. Karakter: Ana karakterin kısa profili.\n";
        $prompt .= "6. Mod (mood): Hikayenin atmosferine uygun tek bir kelime seç: 'action', 'mystery', 'melancholy', 'high-tech', 'corruption'.\n";
        $prompt .= "7. SEO & Sosyal Medya alanlarını doldur.\n\n";
        $prompt .= "Görsel Prompt Kuralları:\n";
        $prompt .= "- Promptlar İNGİLİZCE olmalı.\n";
        $prompt .= "- Stil belirteçleri ekle: 'sgbl artstyle, anime style, studio ghibli, akira style, ghost in the shell style, cel shaded, highly detailed, 8k, vibrant colors'.\n";
        $prompt .= "- Konuşma balonu veya yazı İÇERMEMELİ ('no text, no speech bubbles').\n";
        $prompt .= "- Asla 'photorealistic' veya 'unreal engine' kullanma. Anime estetiğine sadık kal.\n\n";
        $prompt .= "JSON Şeması:\n";
        $prompt .= "{\n";
        $prompt .= "  \"baslik\": \"...\",\n";
        $prompt .= "  \"scenes\": [\n";
        $prompt .= "    { \"text\": \"Sahne 1 metni (TÜRKÇE)...\", \"img_prompt\": \"Visual prompt (ENGLISH)...\" }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"karakter\": \"...\",\n";
        $prompt .= "  \"mood\": \"...\",\n";
        $prompt .= "  \"music_prompt\": \"Music description (English). E.g: 'Dark synthwave, slow tempo, heavy bass, noir atmosphere'.\",\n";
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
            // Priority 1: Google Gemini (Direct)
            return $this->generateWithGemini($prompt);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Primary (Gemini Direct) Failed: " . $e->getMessage() . ". Switching to OpenRouter...");
            
            try {
                // Priority 2: OpenRouter (DeepSeek Chimera - Free & Smart)
                return $this->generateWithOpenRouter($prompt, 'tngtech/deepseek-r1t2-chimera:free');
            } catch (\Exception $e2) {
                 \Illuminate\Support\Facades\Log::warning("Backup 1 (DeepSeek) Failed: " . $e2->getMessage() . ". Switching to Gemini Free Tier via OpenRouter...");

                 // Priority 3: OpenRouter (Gemini 2.0 Flash - Free)
                 // This uses OpenRouter's pool, which might override the direct Google 429 limit
                 return $this->generateWithOpenRouter($prompt, 'google/gemini-2.0-flash-exp:free');
            }
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

        // Increased to 600s (10 mins) for DeepSeek/OpenRouter to prevent timeouts
        $response = Http::timeout(600)->withHeaders([
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
        
        // Determine if it starts with [ (Array) or { (Object)
        $firstBracket = strpos($text, '[');
        $firstBrace = strpos($text, '{');
        
        // If Array is first (or only one existing)
        if ($firstBracket !== false && ($firstBrace === false || $firstBracket < $firstBrace)) {
            if (preg_match('/\[.*\]/s', $text, $matches)) {
                $text = $matches[0];
            }
        } 
        // If Object is first
        elseif ($firstBrace !== false) {
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $text = $matches[0];
            }
        }
        
        // Attempt to decode
        $data = json_decode($text, true);
        
        // Repair Strategy: If Syntax Error and looks like an array, try to salvage items
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (strpos(trim($text), '[') === 0) {
                 // It's likely an array. Use regex to extract all full {...} objects
                 preg_match_all('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $text, $matches);
                 if (!empty($matches[0])) {
                     $repairedJson = '[' . implode(',', $matches[0]) . ']';
                     $data = json_decode($repairedJson, true);
                 }
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Last Resort: Check if it was cut off at the end
            $lastBrace = strrpos($text, '}');
            if ($lastBrace !== false) {
                 $cutText = substr($text, 0, $lastBrace + 1);
                 // If it started with [, we need to close it
                 if (strpos(trim($text), '[') === 0) {
                     $cutText .= ']';
                 }
                 $data = json_decode($cutText, true);
            }
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                  throw new \Exception('Invalid JSON syntax (' . json_last_error_msg() . '). Content: ' . substr($text, 0, 100) . '...');
            }
        }

        // Critical Check: Ensure 'scenes' exists ONLY if we expect it (context dependent)
        // But for comments, we don't need scenes. This validation below was too strict for generic usage.
        // We will make it conditional or just generic check.
        // if (!isset($data['scenes']) ... ) // REMOVED THIS STRICT CHECK as it breaks generic usage
        
        // Pass constraints...
        
        // Pass constraints to the controller via a special key (not used by AI directly but by our app)
        if(isset($visualConstraints) && !empty($visualConstraints)) {
            $data['meta_visual_prompts'] = implode(", ", $visualConstraints);
        }

        return $data;
    }

    public function generateImage(string $prompt, string $visualPrompt = null, string $refImageUrl = null): string
    {
        // Use Pollinations.ai with FLUX model (State of the Art)
        // Updated Style: Strong Anime / Manga (2D Flat)
        $style = ", cyberpunk, vibrant colors, detailed line art, 8k, atmospheric, masterpiece, no text";
        
        // Negative Prompt (Standard for Polli/SD)
        // Note: Pollinations might require appending negative prompts directly or via parameter.
        // We will append it to the URL query string if supported, OR embed it in prompt with negative syntax if model supports it.
        // For Flux/Turbo on Pollinations, we can try '&negative='
        $negative = "bad anatomy, extra limbs, extra fingers, 4 arms, 10 fingers, mutated hands, poorly drawn face, deformed, text, watermark, signature, blurry, low quality, 3d, realistic, photorealistic, cgi, unity render, unreal engine, ugly, worst quality";

        // 1. Inject Visual Consistency Prompt
        if ($visualPrompt) {
            $prompt .= ", " . $visualPrompt;
        }

        if (!str_starts_with(strtolower($prompt), "sgbl artstyle")) {
            $prompt = "sgbl artstyle, " . $prompt;
        }
        $encodedPrompt = urlencode($prompt . $style);
        $encodedNegative = urlencode($negative);
        
        // 2. Inject Reference Image (Experimental Support in Pollinations/Flux)
        // If the model supports img2img via URL, we append it. For now, Pollinations uses strict text-to-image mostly.
        // However, we can try appending the image URL to the seed or separate param if supported.
        // Current Strategy: Strong Prompting (Visual Prompt) is safer.
        // Future: If local Stable Diffusion, we would pass init_image.
        
        // Added '&model=flux' for better quality (Replace turbo)
        // Added '&enhance=true' (Pollinations feature)
        // Added '&negative=' parameter
        return "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=1280&height=720&nologo=true&model=flux&seed=" . rand(1, 99999) . "&negative={$encodedNegative}";
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
        
        // Ensure directory exists in PUBLIC folder (No Symlink needed)
        // $path comes as "stories/2025-01-01/slug_0.jpg"
        $fullPath = public_path($path);
        
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($fullPath, $contents);
        
        // Return direct URL
        return url($path);
    }

    /**
     * Translate Story Content
     */
    public function translateContent(string $title, string $content, string $summary, string $targetLang = 'English'): array
    {
        try {
            // 1. Translate Title
            $titlePrompt = "Translate the following title from Turkish to {$targetLang}. Output ONLY the translated title, no quotes, no explanations.\nText: {$title}";
            $transTitle = $this->generateRawWithOpenRouter($titlePrompt, 'tngtech/deepseek-r1t2-chimera:free');
            $transTitle = trim($transTitle, " \"'\n\r\t\v\0");

            // 2. Translate Summary
            $summaryPrompt = "Translate the following summary from Turkish to {$targetLang}. Output ONLY the translated text.\nText: {$summary}";
            $transSummary = $this->generateRawWithOpenRouter($summaryPrompt, 'tngtech/deepseek-r1t2-chimera:free');

            // 3. Translate Content (HTML aware)
             preg_match_all('/<p>(.*?)<\/p>/s', $content, $matches);
             $paragraphs = $matches[1] ?? [];
             
             if (count($paragraphs) < 2) {
                 $contentPrompt = "Translate this HTML content from Turkish to {$targetLang}. Keep HTML tags (like <div>, <p>, <img>) EXACTLY as they are. Translate only the text.\n\nContent:\n{$content}";
                 $transContent = $this->generateRawWithOpenRouter($contentPrompt, 'tngtech/deepseek-r1t2-chimera:free');
             } else {
                 $transContent = $content;
                 $chunks = array_chunk($paragraphs, 5);
                 
                 foreach ($chunks as $chunk) {
                     $textBlock = implode("\n|||\n", $chunk);
                     $chunkPrompt = "Translate the following text blocks from Turkish to {$targetLang}. The blocks are separated by '|||'. Keep the separator in output. Output ONLY the translated blocks.\n\n{$textBlock}";
                     
                     try {
                         $response = $this->generateRawWithOpenRouter($chunkPrompt, 'tngtech/deepseek-r1t2-chimera:free');
                         $transBlocks = explode("|||", $response);
                         
                         foreach ($chunk as $index => $original) {
                             if (isset($transBlocks[$index])) {
                                 $transContent = str_replace($original, trim($transBlocks[$index]), $transContent);
                             }
                         }
                     } catch (\Exception $e) {
                         Log::warning("Chunk translation failed: " . $e->getMessage());
                     }
                 }
             }

            return [
                'title' => !empty($transTitle) ? $transTitle : $title,
                'content' => !empty($transContent) ? $transContent : $content,
                'summary' => !empty($transSummary) ? $transSummary : $summary
            ];

        } catch (\Exception $e) {
            Log::error("Translation Major Failure: " . $e->getMessage());
             return [
                'title' => $title . " [ERR: " . substr($e->getMessage(), 0, 20) . "]", 
                'content' => $content,
                'summary' => $summary
            ];
        }
    }

    protected function generateRawWithGemini($prompt)
    {
         // Legacy Gemini Implementation (Hidden)
         if (!$this->apiKey) throw new \Exception('GEMINI_API_KEY missing');
         // ... (Retry logic kept in case we switch back)
         // For brevity, skipping full implementation here since we switched to OpenRouter above
         return ""; 
    }

    public function generateRawWithOpenRouter($prompt, $model) {
        $key = config('services.openrouter.key');
        if(!$key) throw new \Exception('OPENROUTER_KEY missing');
        
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;
            
            $response = Http::timeout(600)->withHeaders([
                'Authorization' => "Bearer $key",
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'max_tokens' => 2500, // Ensure enough length
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? '';
            }
            
            if ($response->status() == 429) {
                Log::warning("OpenRouter Rate Limit. Retrying in " . ($attempt * 5) . "s...");
                sleep($attempt * 5);
                continue;
            }
            
             throw new \Exception('OpenRouter Error: ' . $response->status());
        }
        
         throw new \Exception('OpenRouter Failed after 3 retries.');
    }

    public function generateComments(string $storySummary, string $mood): array
    {
        $prompt = "Generate 3-5 fictional user comments (Turkish language) for a cyberpunk story with the following summary:\n";
        $prompt .= "Summary: $storySummary\n";
        $prompt .= "Mood: $mood\n\n";
        $prompt .= "The comments should be from 'netizens' of a dystopian city. Mix of slang, tech-speak, and philosophical dread.\n";
        $prompt .= "Personas:\n1. The Skeptic (doubts the truth)\n2. The Fan (loves the action)\n3. The Doomer (depressed)\n4. The Glitch (speaks in cryptic code)\n\n";
        $prompt .= "Output JSON format only: [{ \"user\": \"Nickname\", \"text\": \"Comment content\" }]";

        try {
            // Reverting to DeepSeek (Free) now that we have Repair Logic + Max Tokens
            $model = 'tngtech/deepseek-r1t2-chimera:free'; 
            $rawResponse = $this->generateRawWithOpenRouter($prompt, $model);
            
            // Debug for CLI
            if (php_sapi_name() === 'cli') {
                echo "\n[DEBUG] Raw AI Response:\n" . substr($rawResponse, 0, 500) . "...\n";
            }

            $data = $this->cleanAndDecodeJson($rawResponse);

            // Handle wrapped response (e.g. { "comments": [...] })
            if (isset($data['comments']) && is_array($data['comments'])) {
                return $data['comments'];
            }

            // Handle direct array
            if (is_array($data) && isset($data[0])) {
                return $data;
            }

            // Fallback: If AI returned a single object instead of array
            if (is_array($data) && isset($data['user'])) {
                return [$data];
            }
            
            if (php_sapi_name() === 'cli') {
                 echo "\n[DEBUG] JSON Structure Mismatch: " . json_encode($data) . "\n";
            }

            Log::error("Comment JSON Structure Invalid: " . json_encode($data));
            return [];
            
        } catch (\Exception $e) {
            if (php_sapi_name() === 'cli') {
                 echo "\n[DEBUG] Exception: " . $e->getMessage() . "\n";
            }
            Log::error("Comment Generation Failed: " . $e->getMessage());
            return [];
        }
    }

    public function generateMusic(string $prompt, string $duration = "30"): string
    {
        // Placeholder for ComfyUI Music Generation
        // Returns empty string to allow fallback to mood-based tracks
        return "";
    }

    public function compileAnthology(string $storiesText, int $volume, int $part, int $totalParts): string
    {
        $prompt = "Sen 'Neo-Pera' evreninin Baş Editörüsün. Şu an 20 hikayelik bir romanın $part. Kısmını ($part / $totalParts) düzenliyorsun. Elindeki metinleri (5 Hikaye) akıcı bir şekilde birbirine bağla.\n\n";
        $prompt .= "--- BAĞLAM ---\n";
        $prompt .= "Bu metinler 'Neo-Pera Chronicles: Cilt $volume' kitabının bir parçasıdır. Önceki ve sonraki parçalarla uyumlu, karanlık, siberpunk bir atmosfer yarat.\n\n";
        $prompt .= "--- HEDEF ---\n";
        $prompt .= "1. Hikayelerin orijinal metinlerini KORU ama aralarındaki geçişleri yumuşat. 'Bölüm X' şeklinde ayır.\n";
        $prompt .= "2. Her hikayenin başına kısa bir 'Tarihçe/Log' notu ekle (Örn: Cycle 2077, Sector 4).\n";
        $prompt .= "3. Sadece HTML gövdesini ver (div, p, h2 vb).\n\n";
         
        if($part === 1) {
            $prompt .= "4. BAŞLANGIÇ: Roman için etkileyici bir <h1>Başlık</h1> ve etkileyici bir Önsöz (Prologue) yaz.\n";
        }
        if($part === $totalParts) {
            $prompt .= "4. BİTİŞ: Romanı sonlandıran kısa bir Sonsöz (Epilogue) yaz.\n";
        }

        $prompt .= "\n--- İÇERİK (HİKAYELER) ---\n";
        $prompt .= $storiesText . "\n\n";
        $prompt .= "--- ÇIKTI (TÜRKÇE HTML) ---";

        // Use DeepSeek (OpenRouter) as Primary
        try {
            return $this->generateRawWithOpenRouter($prompt, 'tngtech/deepseek-r1t2-chimera:free');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Primary Model (DeepSeek) Failed for Anthology: " . $e->getMessage() . ". Switching to Backup (Gemini)...");
            
            // Backup: Gemini 2.0 Flash (Free Model on OpenRouter)
            // Or use Google AI Studio directly if configured, but let's stick to OpenRouter for consistency if key is same
            // Actually generateRawWithOpenRouter handles OpenRouter keys. 
            // Let's use 'google/gemini-2.0-flash-exp:free' on OpenRouter.
            return $this->generateRawWithOpenRouter($prompt, 'google/gemini-2.0-flash-exp:free');
        }
    }

    protected function getMockData(): array
    {
        return []; // Mock data deprecated
    }
}
