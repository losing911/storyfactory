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
    protected $signature = 'app:generate-daily-story';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Günlük Cyberpunk hikayesini oluşturur ve yayınlar.';

    /**
     * Execute the console command.
     */
    public function handle(AIService $aiService, SocialPosterService $socialPoster)
    {
        // Increase time limit to 10 minutes
        set_time_limit(600);
        
        $this->info('Günlük Cyberpunk Çizgi Roman üretimi başlıyor...');
        \Illuminate\Support\Facades\Log::info('Daily Story Auto-Gen Started (Schedule/Command)');

        try {
            // 1. Generate Story Structure (JSON)
            $data = $aiService->generateFullStory();
            
            if (!isset($data['scenes']) || !is_array($data['scenes'])) {
                throw new \Exception("AI yanıtı beklenen 'scenes' formatında değil.");
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
                $prompt = $scene['img_prompt'];
                $text = $scene['text'];
                
                // Get Visual Constraints
                $visualConstraints = $data['meta_visual_prompts'] ?? null;

                // Rate Limiting
                sleep(4);

                try {
                    $remoteUrl = $aiService->generateImage($prompt, $visualConstraints);
                    $localPath = "stories/$dateFolder/{$slug}_{$index}.jpg";
                    $localUrl = $aiService->downloadImage($remoteUrl, $localPath);
                } catch (\Exception $e) {
                    $localUrl = "https://placehold.co/1280x720/050505/00ff00?text=Error"; 
                }

                $storyHtml .= "<div class='scene-container mb-12 p-4 bg-gray-900/50 rounded-lg border border-gray-800'>";
                $storyHtml .= "  <div class='mb-4'><img src='$localUrl' alt='Scene $index' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500'></div>";
                $storyHtml .= "  <div class='prose prose-invert prose-lg text-gray-300 font-sans leading-relaxed'><p>" . nl2br(e($text)) . "</p></div>";
                $storyHtml .= "</div>";

                if ($index === 0) {
                    $coverImageUrl = $localUrl;
                }
                
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            // 3. Save to DB
            $storyData = [
                'baslik' => $data['baslik'],
                'slug' => $slug,
                'metin' => $storyHtml,
                'gorsel_url' => $coverImageUrl,
                'yayin_tarihi' => now(),
                'durum' => 'published',
                'konu' => 'AI Auto-Gen',
                'mood' => $data['mood'] ?? 'mystery',
                'meta' => ($data['meta_baslik'] ?? '') . ' | ' . ($data['meta_aciklama'] ?? ''),
                'etiketler' => $data['etiketler'] ?? [],
                'sosyal_ozet' => $data['sosyal_ozet'] ?? '',
                'gorsel_prompt' => json_encode(array_column($data['scenes'], 'img_prompt')),
            ]; // Array closed correctly

            $story = Story::create($storyData);
            
            // 4. Auto-Translate to English
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
                                $lorePrompt = "Portrait of " . $loreItem['title'] . ", " . ($loreItem['visual_prompt'] ?? $loreItem['description']) . ", cyberpunk 2077 style, detailed, 8k";
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

            // 6. Post to Social Media
            $socialPoster->postToSocialMedia($story);

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
