<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoreEntry;
use App\Services\AIService;

class FillLoreImages extends Command
{
    protected $signature = 'lore:fill-images';
    protected $description = 'Generates missing images for Lore entries using AIService';

    public function handle(AIService $aiService)
    {
        $entries = LoreEntry::whereNull('image_url')->orWhere('image_url', '')->get();

        $this->info("Found " . $entries->count() . " lore entries without images.");

        foreach ($entries as $entry) {
            $this->info("Generating image for: {$entry->title} ({$entry->type})");
            
            try {
                // Construct Prompt
                $prompt = "sgbl artstyle, Cyberpunk 2077 concept art, " . $entry->type . ", " . $entry->title . ", " . ($entry->visual_prompt ?? $entry->description);
                $prompt = substr($prompt, 0, 400); // Limit length

                // Generate (Synchronous Pollinations)
                $remoteUrl = $aiService->generateImage($prompt);
                
                // Download
                $slug = $entry->slug ?? \Illuminate\Support\Str::slug($entry->title);
                $localPath = "lore/generated_{$slug}_" . time() . ".jpg";
                $localUrl = $aiService->downloadImage($remoteUrl, $localPath);

                // Save
                $entry->image_url = $localUrl;
                $entry->save();
                
                $this->info("Saved: $localUrl");
                
            } catch (\Exception $e) {
                $this->error("Failed for {$entry->title}: " . $e->getMessage());
            }
        }
    }
}
