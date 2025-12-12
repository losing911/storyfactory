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
        $this->info('Günlük Cyberpunk Çizgi Roman üretimi başlıyor...');

        try {
            // 1. Generate Story Structure (JSON)
            $data = $aiService->generateFullStory();
            
            // Check if 'scenes' exists
            if (!isset($data['scenes']) || !is_array($data['scenes'])) {
                throw new \Exception("AI yanıtı beklenen 'scenes' formatında değil.");
            }

            $storyHtml = "";
            $coverImageUrl = null;
            $slug = \Illuminate\Support\Str::slug($data['baslik'] ?? 'daily-story-' . now()->timestamp);
            $dateFolder = now()->format('Y-m-d');
            $files = [];

            $this->info("Hikaye: {$data['baslik']}");
            $bar = $this->output->createProgressBar(count($data['scenes']));
            $bar->start();

            // 2. Process Scenes
            foreach ($data['scenes'] as $index => $scene) {
                $prompt = $scene['img_prompt'];
                $text = $scene['text'];
                
                $text = $scene['text'];
                
                // Get Visual Constraints
                $visualConstraints = $data['meta_visual_prompts'] ?? null;

                try {
                    // Generate Image URL
                    $remoteUrl = $aiService->generateImage($prompt, $visualConstraints);
                    
                    // Download Image Locally
                    $localPath = "stories/$dateFolder/{$slug}_{$index}.jpg";
                    $localUrl = $aiService->downloadImage($remoteUrl, $localPath);
                } catch (\Exception $e) {
                    $this->error("Görsel Hatası (Sahne $index): " . $e->getMessage());
                    // Fallback to a placeholder or skip image
                    $localUrl = "https://placehold.co/1280x720/050505/00ff00?text=Cyberpunk+Image+Error"; 
                }

                // Determine Layout
                $layoutClass = ($index % 2 == 0) ? 'flex-row' : 'flex-row-reverse';

                // Append to Story HTML
                $storyHtml .= "<div class='scene-container mb-12 p-4 bg-gray-900/50 rounded-lg border border-gray-800'>";
                $storyHtml .= "  <div class='mb-4'><img src='$localUrl' alt='Scene $index' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500'></div>";
                $storyHtml .= "  <div class='prose prose-invert prose-lg text-gray-300 font-sans leading-relaxed'><p>" . nl2br(e($text)) . "</p></div>";
                $storyHtml .= "</div>";

                // Use the first image as cover (or fallback if first failed)
                if ($index === 0) {
                    $coverImageUrl = $localUrl;
                }
                
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            // 3. Save to DB
            $story = Story::create([
                'baslik' => $data['baslik'],
                'slug' => $slug,
                'metin' => $storyHtml,
                'gorsel_url' => $coverImageUrl,
                'yayin_tarihi' => now(),
                'durum' => 'published',
                'konu' => 'AI Auto-Gen',
                'mood' => $data['mood'] ?? 'mystery', // Default to mystery if missing
                'meta' => ($data['meta_baslik'] ?? '') . ' | ' . ($data['meta_aciklama'] ?? ''),
                'etiketler' => $data['etiketler'] ?? [],
                'sosyal_ozet' => $data['sosyal_ozet'] ?? '',
                'gorsel_prompt' => json_encode(array_column($data['scenes'], 'img_prompt')),
            ]);

            $this->info("Veritabanına kaydedildi: ID #{$story->id}");

            // 4. Post to Social Media
            $socialPoster->postToSocialMedia($story);

            $this->info('Otomasyon Başarılı!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Otomasyon Hatası: ' . $e->getMessage());
            return 1;
        }
    }
}
