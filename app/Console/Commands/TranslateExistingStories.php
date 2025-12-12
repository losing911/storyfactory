<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslateExistingStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:translate-existing-stories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translates existing stories to English if translation is missing.';

    public function handle(\App\Services\AIService $aiService)
    {
        $stories = \App\Models\Story::where('durum', 'published')->get();
        $this->info("Found {$stories->count()} published stories. Checking for translations...");

        $bar = $this->output->createProgressBar($stories->count());
        $bar->start();

        foreach ($stories as $story) {
            // Check if EN translation exists
            if ($story->translations()->where('locale', 'en')->exists()) {
                $bar->advance();
                continue;
            }

            try {
                // Translate
                // $this->line(" Translating ID: {$story->id}...");
                $translated = $aiService->translateContent($story->baslik, $story->metin, $story->sosyal_ozet ?? '', 'English');
                
                if(!empty($translated['title'])) {
                    $story->translations()->create([
                        'locale' => 'en',
                        'title' => $translated['title'],
                        'metin' => $translated['content'],
                        'social_ozet' => $translated['summary'] ?? ''
                    ]);
                }
                
                // Sleep to avoid Rate Limits (Gemini is 60 RPM but we play safe)
                sleep(2);

            } catch (\Exception $e) {
                $this->error("Failed to translate Story ID {$story->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All stories checked and translated!');
    }
}
