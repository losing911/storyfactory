<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoreEntry;
use App\Services\AIService;
use Illuminate\Support\Str;

class GenerateLoreImages extends Command
{
    protected $signature = 'lore:generate-images {--force : Force regenerate all images}';
    protected $description = 'Generate missing images for Lore entries using AI';

    public function handle(AIService $aiService)
    {
        $this->info('Starting Lore Image Generation...');

        $query = LoreEntry::query();

        if (!$this->option('force')) {
            $query->whereNull('image_url')->orWhere('image_url', '');
        }

        $entries = $query->get();
        $count = $entries->count();

        if ($count === 0) {
            $this->info('No lore entries found needing images.');
            return;
        }

        $this->info("Found {$count} entries. Processing...");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($entries as $lore) {
            try {
                $this->line("\nGenerating for: {$lore->title} ({$lore->type})");

                // Construct Prompt
                $visualDesc = $lore->visual_prompt ?? $lore->description;
                $prompt = "sgbl artstyle, Portrait of {$lore->title}, a cyberpunk {$lore->type}, {$visualDesc}, detailed, 8k, cinematic lighting, neon atmosphere";
                
                // Enhance prompt based on type
                if ($lore->type === 'city' || $lore->type === 'location') {
                    $prompt = "sgbl artstyle, Cyberpunk city location, {$lore->title}, {$visualDesc}, futuristic architecture, neon signs, rainy streets, detailed environment";
                }

                // Generate
                $remoteUrl = $aiService->generateImage($prompt);
                
                // Download
                $filename = Str::slug($lore->title) . '_' . uniqid() . '.jpg';
                $localPath = "lore/{$filename}";
                $localUrl = $aiService->downloadImage($remoteUrl, $localPath);

                // Update DB
                $lore->update(['image_url' => $localUrl]);
                
            } catch (\Exception $e) {
                $this->error("\nFailed for {$lore->title}: " . $e->getMessage());
            }

            $bar->advance();
            sleep(2); // Rate limiting
        }

        $bar->finish();
        $this->newLine();
        $this->info('Lore Image Generation Completed!');
    }
}
