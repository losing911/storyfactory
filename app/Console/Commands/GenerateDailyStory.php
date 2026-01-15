<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use App\Services\AIService;
use App\Services\SocialPosterService;

class GenerateDailyStory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-daily-story {--draft : Taslak olarak oluştur (Yayınlama)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Günlük Cyberpunk hikayesini oluşturur ve yayınlar (veya taslak bırakır).';

    /**
     * Execute the console command.
     */
    public function handle(AIService $aiService, SocialPosterService $socialPoster, \App\Services\SunoService $sunoService)
    {
        // Increase time limit to 10 minutes
        set_time_limit(600);
        
        $isDraft = $this->option('draft');
        $this->info('Günlük Cyberpunk Çizgi Roman üretimi başlıyor...' . ($isDraft ? ' (TASLAK MODU)' : ''));
        \Illuminate\Support\Facades\Log::info('Daily Story Auto-Gen Started ' . ($isDraft ? '(Draft)' : ''));
        \Illuminate\Support\Facades\Artisan::call('optimize:clear'); // Ensure no stale cache causes syntax errors

        try {
            // 1. Generate Story Structure (JSON) with Retries
            $maxRetries = 3;
            $data = [];
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $this->info("AI Hikaye Oluşturuyor... (Deneme $attempt/$maxRetries)");
                    $data = $aiService->generateFullStory();
                    
                    if (!isset($data['scenes']) || !is_array($data['scenes'])) {
                        throw new \Exception("AI yanıtı beklenen 'scenes' formatında değil.");
                    }
                    
                    // If successful, break loop
                    break;
                } catch (\Exception $e) {
                    $this->warn("Deneme $attempt Başarısız: " . $e->getMessage());
                    if ($attempt === $maxRetries) {
                        throw $e; // Throw on final failure
                    }
                    sleep(2); // Wait before retry
                }
            }

            $storyHtml = "";
            $coverImageUrl = null;
            $slug = \Illuminate\Support\Str::slug($data['baslik'] ?? 'daily-story-' . now()->timestamp);
            $dateFolder = now()->format('Y-m-d');

            $this->info("Hikaye: {$data['baslik']}");
            $bar = $this->output->createProgressBar(count($data['scenes']));
            $bar->start();

            // 2. Process Scenes
            foreach ($data['scenes'] as $index => $scene) {
                // WORKER MODE: Skip synchronous generation
                // $prompt = $scene['img_prompt'];
                // $remoteUrl = $aiService->generateImage($prompt, $data['meta_visual_prompts'] ?? null);
                // $localUrl = $aiService->downloadImage($remoteUrl, "stories/$dateFolder/{$slug}_{$index}.jpg");
                
                // Use Placeholder for now, Worker will update Cover later.
                // For inner scenes, we keep placeholders or maybe update them later too (Phase 2).
                $localUrl = "https://placehold.co/1280x720/1f2937/00ff00?text=Generating+Visuals...";

                $storyHtml .= "<div class='scene-container mb-12 p-4 bg-gray-900/50 rounded-lg border border-gray-800'>";
                $storyHtml .= "  <div class='mb-4'><img src='$localUrl' alt='Scene $index' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500'></div>";
                $storyHtml .= "  <div class='prose prose-invert prose-lg text-gray-300 font-sans leading-relaxed'><p>" . nl2br(e($scene['text'])) . "</p></div>";
                $storyHtml .= "</div>";
                
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            // 3. Save to DB
            $storyData = [
                'baslik' => $data['baslik'],
                'slug' => $slug,
                'metin' => $storyHtml, // Contains placeholders
                'gorsel_url' => null,  // Will be filled by Worker
                'yayin_tarihi' => now(),
                'durum' => $isDraft ? 'draft' : 'pending_visuals', // TRIGGER THE WORKER
                'konu' => 'AI Auto-Gen',
                'mood' => $data['mood'] ?? 'mystery',
                'mood' => $data['mood'] ?? 'mystery',
                'meta' => \Illuminate\Support\Str::limit(($data['meta_baslik'] ?? '') . ' | ' . ($data['meta_aciklama'] ?? ''), 250),
                'etiketler' => $data['etiketler'] ?? [],
                'sosyal_ozet' => \Illuminate\Support\Str::limit($data['sosyal_ozet'] ?? '', 250),
                'gorsel_prompt' => json_encode(array_column($data['scenes'], 'img_prompt')),
                'music_prompt' => $data['music_prompt'] ?? null,
                'author_id' => \App\Models\Author::inRandomOrder()->first()->id ?? null,
            ];

            // 3.1 Generate Music (Suno)
            if (!empty($data['music_prompt'])) {
                $this->info("Müzik Üretiliyor: " . $data['music_prompt']);
                try {
                    $musicUrl = $sunoService->generateMusic($data['music_prompt']);
                    if ($musicUrl) {
                        $storyData['music_url'] = $musicUrl;
                        $this->info("Müzik Başarıyla Oluşturuldu: $musicUrl");
                    } else {
                        $this->warn("Müzik oluşturulamadı (URL boş).");
                    }
                } catch (\Exception $musicErr) {
                    $this->error("Müzik Hatası: " . $musicErr->getMessage());
                }
            }
            
            // 3.3 SAVE STORY TO DB (CRITICAL FIX)
            $story = Story::create($storyData);
            $this->info("Hikaye Veritabanına Kaydedildi: ID {$story->id}");
            
            // 3.2 Auto-Comments (REMOVED: Moved to manual command story:simulate-comments)
            // $this->info("Netizen Yorumları Simüle Ediliyor...");
            // ...
            
            // 4. Auto-Translate to English (DISABLED BY USER REQUEST)
            // User requested to use Google Translate Widget instead to save AI resources.
            /*
            try {
                $this->info('İngiliz,ce Çeviri Başlatılıyor...');
                // Strip HTML for translation context if needed, but we asked AI to keep HTML.
                // Pass $storyHtml directly.
                $translated = $aiService->translateContent($story->baslik, $storyHtml, $data['sosyal_ozet'] ?? '', 'English');
                
                if(!empty($translated['title'])) {
                    $story->translations()->create([
                        'locale' => 'en',
                        'title' => $translated['title'],
                        'metin' => $translated['content'],
                        'social_ozet' => $translated['summary'] ?? ''
                    ]);
                    $this->info('İngilizce Çeviri Tamamlandı!');
                    \Illuminate\Support\Facades\Log::info("Story Translated to EN: ID {$story->id}");
                }
            } catch (\Exception $e) {
                $this->error('Çeviri Hatası: ' . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Translation Failed: " . $e->getMessage());
            }
            */
            
            // 5. Process New Lore (Auto-Extraction)
            if (!empty($data['new_lore']) && is_array($data['new_lore'])) {
                foreach ($data['new_lore'] as $loreItem) {
                    try {
                        if (empty($loreItem['title']) || empty($loreItem['type'])) continue;
                        $loreSlug = \Illuminate\Support\Str::slug($loreItem['title']);
                        
                        // Check uniqueness
                        if (!\App\Models\LoreEntry::where('slug', $loreSlug)->exists()) {
                            
                            $loreImageUrl = null;

                            // Generate Image for Lore
                            try {
                                $lorePrompt = "sgbl artstyle, Portrait of " . $loreItem['title'] . ", " . ($loreItem['visual_prompt'] ?? $loreItem['description']) . ", cyberpunk 2077 style, detailed, 8k";
                                $remoteLoreUrl = $aiService->generateImage($lorePrompt);
                                $localLorePath = "lore/" . $loreSlug . "_" . now()->timestamp . ".jpg";
                                $loreImageUrl = $aiService->downloadImage($remoteLoreUrl, $localLorePath);
                                $this->info("Lore Görseli Oluşturuldu: {$loreItem['title']}");
                            } catch (\Exception $imgErr) {
                                \Illuminate\Support\Facades\Log::warning("Lore Image Failed: " . $imgErr->getMessage());
                            }

                            \App\Models\LoreEntry::create([
                                'title' => $loreItem['title'],
                                'slug' => $loreSlug,
                                'type' => strtolower($loreItem['type']) === 'location' ? 'city' : strtolower($loreItem['type']),
                                'description' => $loreItem['description'] ?? 'AI tarafından keşfedildi.',
                                'visual_prompt' => $loreItem['visual_prompt'] ?? null,
                                'image_url' => $loreImageUrl,
                                'is_active' => true
                            ]);
                            \Illuminate\Support\Facades\Log::info("New Lore Auto-Discovered: {$loreItem['title']}");
                        }
                    } catch (\Exception $e) { 
                        \Illuminate\Support\Facades\Log::error("Lore Entry Error: " . $e->getMessage());
                    }
                }
            }

            // 6. Post to Social Media (Laravel Service - Legacy/Internal)
            $socialPoster->postToSocialMedia($story);
            
            // 7. Trigger Python Twitter Bot (Official API + Trends)
            $this->info('Twitter Bot Tetikleniyor...');
            try {
                // Determine python command (python3 on server, python on windows)
                $pythonCmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
                $scriptPath = base_path('twitter_bot.py');
                
                // Execute command asynchronously or synchronously? Sync is safer for logging.
                // Redirect output to verify it runs
                $output = [];
                $returnVar = 0;
                exec("$pythonCmd $scriptPath 2>&1", $output, $returnVar);
                
                // Log output
                foreach ($output as $line) {
                    $this->line("  [Bot]: $line");
                    \Illuminate\Support\Facades\Log::info("Twitter Bot Output: $line");
                }
                
                if ($returnVar === 0) {
                    $this->info('Twitter Paylaşımı Başarılı!');
                } else {
                    $this->warn('Twitter Botu Hata Verdi (Exit Code: ' . $returnVar . ')');
                }
                
            } catch (\Exception $e) {
                $this->error('Twitter Bot Başlatılamadı: ' . $e->getMessage());
            }

            $this->info('Otomasyon Başarılı!');
            \Illuminate\Support\Facades\Log::info("Daily Story Created Successfully: ID {$story->id}");
            return 0;

        } catch (\Exception $e) {
            $this->error('Otomasyon Hatası: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Daily Story Auto-Gen FAILED: ' . $e->getMessage());
            return 1;
        }
    }
}
