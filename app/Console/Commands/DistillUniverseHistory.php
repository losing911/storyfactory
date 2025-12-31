<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DistillUniverseHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:distill-universe-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Initiating Universe Distillation Protocol...");

        // 1. Fetch Stories (Optimization: Get latest 150 to cover more history)
        $stories = \App\Models\Story::where('durum', 'published')
            ->latest()
            ->take(150)
            ->get(['baslik', 'sosyal_ozet', 'mood', 'yayin_tarihi']);

        if ($stories->isEmpty()) {
            $this->error("No stories found to distill.");
            return;
        }

        $this->info("Feeding " . $stories->count() . " stories into the Neural Net...");

        // 2. Prepare Context
        $context = "";
        foreach ($stories as $story) {
            $context .= "- [{$story->yayin_tarihi->format('Y-m-d')}] {$story->baslik}: {$story->sosyal_ozet} (Mood: {$story->mood})\n";
        }

        // 3. Build AI Prompt
        $prompt = "You are the 'Grand Historian' (Chronicler) of the Cyberpunk city 'Neo-Pera'.\n";
        $prompt .= "Below is a chronological list of 150 recent events (stories) that happened in the city:\n\n";
        $prompt .= $context;
        $prompt .= "\n\nTASK:\n";
        $prompt .= "1. Analyze these events to find connecting threads, recurring conflicts, or shifts in power.\n";
        $prompt .= "2. Write a 'State of the Universe' report (Title: 'The Neo-Pera Chronicles: Current Era').\n";
        $prompt .= "3. Format it as an HTML section. Use <h3> for Chapter Titles. Use <blockquote> for important character quotes or 'system logs'. Use <strong> to highlight key terms (like 'Sendika', 'Glitch').\n";
        $prompt .= "4. Theme: Dark, gritty, analytical but atmospheric. Mention 'The Syndicate', 'Resistance', and 'The Glitch' if they fit.\n";
        $prompt .= "5. Output ONLY the HTML content, no JSON, no markdown code blocks. Do NOT use <h1> or <h2>.\n";
        $prompt .= "6. Language: TURKISH.\n";
        $prompt .= "7. IMPORTANT: The dates provided ([2024-xx-xx]) are Real World Dates. You MUST translate them to the 'Neo-Pera Calendar' by adding 50 years (e.g., 2025 -> 2075). NEVER use 2024/2025 in the output. Use 'Era 2075', 'Cycle 75', or just '2075'.\n";
        $prompt .= "8. LENGTH REQUIREMENT: This must be a 'Long-Form' chronicle. Do not summarize briefly. Create a cohesive story that weaves these events together. Aim for at least 1500 words. Divide into multiple chapters using <h3> tags.\n";

        // 4. Generate with AI Service
        $ai = new \App\Services\AIService();
        // Reflection to access protected method or add public wrapper
        // For now, we assume we can add a public wrapper or use existing generic method if available.
        // Since `generateRawWithOpenRouter` is protected, we need to modify AIService or use a public method.
        // Let's modify AIService later to expose a raw method, or use Reflection here for quick fix.
        
        // Quick Fix: Use Reflection to call protected method
        $reflection = new \ReflectionClass($ai);
        $method = $reflection->getMethod('generateRawWithOpenRouter');
        $method->setAccessible(true);
        
        $this->info("Synthesizing History...");
        try {
            $historyHtml = $method->invokeArgs($ai, [$prompt, 'nex-agi/deepseek-v3.1-nex-n1:free']);
            
            // 5. Update Lore Entry
            // We look for a special Lore Entry with slug 'universe-history' or update the hardcoded view
            // Better: Update a file or a dedicated Lore Entry.
            // Let's create/update a Lore Entry named "Neo-Pera Tarihçesi"
            
            $entry = \App\Models\LoreEntry::updateOrCreate(
                ['slug' => 'neo-pera-tarihcesi'],
                [
                    'title' => 'Neo-Pera Tarihçesi (Canlı)',
                    'type' => 'city', // Special type?
                    'description' => 'Neo-Pera evreninin yapay zeka tarafından derlenen canlı tarihçesi.',
                    'is_active' => false // Hidden from standard lists, used manually
                ]
            );

            // Store the HTML in a special field or description?
            // Since LoreEntry doesn't have 'content', we might need to save this to a file
            // OR update the description if it's long enough. 
            // Let's save it to a partial view file! 'resources/views/lore/partials/history_generated.blade.php'
            
            $path = resource_path('views/lore/partials/history_generated.blade.php');
            if(!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
            
            file_put_contents($path, $historyHtml);
            
            $this->info("History updated successfully at: $path");
            
        } catch (\Exception $e) {
            $this->error("Distillation Failed: " . $e->getMessage());
        }
    }
}
