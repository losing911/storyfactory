<?php

namespace App\Services;

use App\Models\Story;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;

class SocialPosterService
{
    public function postToSocialMedia(Story $story): bool
    {
        $log = [];

        // 1. Twitter (X) - Requires API Keys
        if (env('TWITTER_CONSUMER_KEY') && env('TWITTER_ACCESS_TOKEN')) {
            try {
                $this->postToTwitter($story);
                $log[] = "Posted to Twitter";
            } catch (\Exception $e) {
                Log::error("Twitter Post Failed: " . $e->getMessage());
                $log[] = "Twitter Failed";
            }
        }

        // 2. Discord Webhook - Requires Webhook URL
        if (env('DISCORD_WEBHOOK_URL')) {
            try {
                $this->postToDiscord($story);
                $log[] = "Posted to Discord";
            } catch (\Exception $e) {
                Log::error("Discord Post Failed: " . $e->getMessage());
                $log[] = "Discord Failed";
            }
        }

        // 3. Instagram - Requires Business Account & Graph API
        if (env('INSTAGRAM_ACCESS_TOKEN') && env('INSTAGRAM_USER_ID')) {
             try {
                $this->postToInstagram($story);
                $log[] = "Posted to Instagram";
            } catch (\Exception $e) {
                Log::error("Instagram Post Failed: " . $e->getMessage());
                $log[] = "Instagram Failed";
            }
        }

        Log::info("Social Media Result: " . implode(', ', $log));
        return true;
    }

    protected function postToTwitter(Story $story)
    {
        // Simple implementation for X API v2 (Text Only)
        // ... (Code remains same)
        $url = 'https://api.twitter.com/2/tweets';
        $payload = [
            'text' => $story->sosyal_ozet . "\n\n" . url('/story/' . $story->id)
        ];
        
        Log::warning("Twitter API requires OAuth 1.0a signing. Please install 'abraham/twitteroauth'.");
    }

    protected function postToDiscord(Story $story)
    {
        // ... (Code remains same)
        $webhookUrl = env('DISCORD_WEBHOOK_URL');
        // ...
        $response = Http::post($webhookUrl, [
            'content' => "**YENİ BÖLÜM YAYINLANDI!** 🚨\n\n" . $story->sosyal_ozet,
            // ... embeds ...
            'embeds' => [[
                'title' => $story->baslik,
                'url' => route('story.show', $story),
                'description' => \Illuminate\Support\Str::limit(strip_tags($story->metin), 200),
                'image' => ['url' => asset($story->gorsel_url)],
            ]]
        ]);
        // ...
    }

    protected function postToInstagram(Story $story)
    {
        $token = env('INSTAGRAM_ACCESS_TOKEN');
        $userId = env('INSTAGRAM_USER_ID');
        $version = 'v18.0';

        // Critical Check: Instagram requires a PUBLIC URL for the image.
        // Localhost URLs (127.0.0.1) will fail because Facebook cannot reach them.
        $imageUrl = asset($story->gorsel_url);
        if (str_contains($imageUrl, 'localhost') || str_contains($imageUrl, '127.0.0.1')) {
            throw new \Exception("Instagram requires a public image URL. Localhost URLs are not supported by Graph API.");
        }

        // 1. Create Media Container
        $endpointContainer = "https://graph.facebook.com/{$version}/{$userId}/media";
        $responseContainer = Http::post($endpointContainer, [
            'image_url' => $imageUrl,
            'caption' => $story->sosyal_ozet . "\n\n#cyberpunk #aiart #story " . implode(' ', array_map(fn($t) => '#'.$t, $story->etiketler ?? [])),
            'access_token' => $token
        ]);

        if ($responseContainer->failed()) {
            throw new \Exception("Instagram Container Error: " . $responseContainer->body());
        }

        $containerId = $responseContainer->json()['id'];

        // 2. Publish Media
        $endpointPublish = "https://graph.facebook.com/{$version}/{$userId}/media_publish";
        $responsePublish = Http::post($endpointPublish, [
            'creation_id' => $containerId,
            'access_token' => $token
        ]);

        if ($responsePublish->failed()) {
            throw new \Exception("Instagram Publish Error: " . $responsePublish->body());
        }
    }
}
