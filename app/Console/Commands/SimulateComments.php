<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use App\Services\AIService;

class SimulateComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'story:simulate-comments {slug? : The slug of the story (optional, defaults to latest)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually generate and inject comments for a story to simulate organic engagement.';

    /**
     * Execute the console command.
     */
    public function handle(AIService $aiService)
    {
        $slug = $this->argument('slug');

        if ($slug) {
            $story = Story::where('slug', $slug)->first();
        } else {
            $story = Story::latest()->first();
        }

        if (!$story) {
            $this->error('Story not found!');
            return 1;
        }

        $this->info("Injecting comments for: {$story->baslik}");
        
        try {
            // Get variation of comments (Skeptic, Fan, hater, etc)
            $mood = $story->mood ?? 'cyberpunk';
            $summary = $story->sosyal_ozet ?? Str::limit(strip_tags($story->metin), 200);

            $this->info('Consulting AI Netizens...');
            $comments = $aiService->generateComments($summary, $mood);

            $count = 0;
            foreach($comments as $c) {
                // Add a random delay to created_at to make it look organic if we were doing this in bulk,
                // but for now strict 'now()' is fine as the command is run manually.
                $story->comments()->create([
                     'nickname' => $c['user'],
                     'message' => $c['text'],
                     'is_approved' => true,
                     'ip_address' => '127.0.0.1'
                ]);
                $this->line("  <info>[+]</info> {$c['user']}: {$c['text']}");
                $count++;
            }

            $this->info("Success! $count comments injected.");
            
        } catch (\Exception $e) {
            $this->error("Failed: " . $e->getMessage());
            return 1;
        }
    }
}
