<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class TestAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-ai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test AI API Connectivity (Gemini & OpenRouter)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting AI Connectivity Test...');
        
        // 1. Check Environment Variables
        $geminiKey = config('services.gemini.key');
        $openRouterKey = config('services.openrouter.key');
        
        $this->info("Checking Environment Variables:");
        $this->line("  - GEMINI_API_KEY: " . ($geminiKey ? "Found (Ends with " . substr($geminiKey, -4) . ")" : "MISSING ❌"));
        $this->line("  - OPENROUTER_API_KEY: " . ($openRouterKey ? "Found (Ends with " . substr($openRouterKey, -4) . ")" : "MISSING ❌"));

        // 2. Test Gemini (Gemini 2.5 Flash - As per AIService)
        $this->newLine();
        $this->info("Testing Google Gemini (Model: gemini-2.5-flash)...");
        if ($geminiKey) {
            try {
                // Testing the model currently used in production
                $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
                $start = microtime(true);
                $response = Http::timeout(10)->post($baseUrl . '?key=' . $geminiKey, [
                    'contents' => [['parts' => [['text' => 'Hello, reply with "OK".']]]]
                ]);
                $duration = round(microtime(true) - $start, 2);

                if ($response->successful()) {
                    $this->info("  ✅ Gemini Success ($duration s)");
                    $this->line("     Response: " . substr($response->body(), 0, 100) . "...");
                } else {
                    $this->error("  ❌ Gemini Failed ($duration s)");
                    $this->line("     Status: " . $response->status());
                    $this->line("     Body: " . $response->body());
                    
                    // Try fallback model if 2.5 fails
                     $this->warn("  ⚠️  Attempting Fallback to gemini-1.5-flash...");
                     $baseUrlFallback = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
                     $responseFallback = Http::timeout(10)->post($baseUrlFallback . '?key=' . $geminiKey, [
                        'contents' => [['parts' => [['text' => 'Hello']]]]
                    ]);
                    if ($responseFallback->successful()) {
                        $this->info("     ✅ Fallback (gemini-1.5-flash) Works!");
                    } else {
                        $this->error("     ❌ Fallback Failed: " . $responseFallback->status());
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Gemini Exception: " . $e->getMessage());
            }
        } else {
            $this->warn("  ⚠️  Skipping Gemini Test (No Key)");
        }

        // 3. Test OpenRouter (DeepSeek)
        $this->newLine();
        $this->info("Testing OpenRouter (Model: tngtech/deepseek-r1t2-chimera:free)...");
        if ($openRouterKey) {
            try {
                $start = microtime(true);
                $response = Http::timeout(15)->withHeaders([
                    'Authorization' => "Bearer $openRouterKey",
                    'HTTP-Referer' => 'https://test.com',
                    'X-Title' => 'Test',
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => 'tngtech/deepseek-r1t2-chimera:free',
                    'messages' => [['role' => 'user', 'content' => 'Hello']],
                ]);
                $duration = round(microtime(true) - $start, 2);

                if ($response->successful()) {
                    $this->info("  ✅ OpenRouter Success ($duration s)");
                    $this->line("     Response: " . substr($response->body(), 0, 100) . "...");
                } else {
                    $this->error("  ❌ OpenRouter Failed ($duration s)");
                    $this->line("     Status: " . $response->status());
                    $this->line("     Body: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("  ❌ OpenRouter Exception: " . $e->getMessage());
            }
        } else {
            $this->warn("  ⚠️  Skipping OpenRouter Test (No Key)");
        }
        
        $this->newLine();
        $this->info("Test Complete.");
    }
}
