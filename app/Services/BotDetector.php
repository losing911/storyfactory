<?php

namespace App\Services;

class BotDetector
{
    /**
     * Known bot patterns and their names
     */
    protected array $botPatterns = [
        // Search Engine Bots
        'googlebot' => 'Googlebot',
        'bingbot' => 'Bingbot',
        'slurp' => 'Yahoo Slurp',
        'duckduckbot' => 'DuckDuckBot',
        'baiduspider' => 'Baiduspider',
        'yandexbot' => 'YandexBot',
        'sogou' => 'Sogou Spider',
        'exabot' => 'Exabot',
        'facebot' => 'Facebook Bot',
        'ia_archiver' => 'Alexa Crawler',
        
        // Social Media Bots
        'facebookexternalhit' => 'Facebook',
        'twitterbot' => 'Twitter',
        'linkedinbot' => 'LinkedIn',
        'pinterest' => 'Pinterest',
        'slackbot' => 'Slack',
        'telegrambot' => 'Telegram',
        'whatsapp' => 'WhatsApp',
        'discordbot' => 'Discord',
        
        // SEO & Analytics Tools
        'semrushbot' => 'SEMrush',
        'ahrefsbot' => 'Ahrefs',
        'mj12bot' => 'Majestic',
        'dotbot' => 'Moz',
        'rogerbot' => 'Moz',
        'screaming frog' => 'Screaming Frog',
        'gtmetrix' => 'GTmetrix',
        
        // Monitoring & Uptime
        'uptimerobot' => 'UptimeRobot',
        'pingdom' => 'Pingdom',
        'statuscake' => 'StatusCake',
        'site24x7' => 'Site24x7',
        'monitis' => 'Monitis',
        'newrelicpinger' => 'New Relic',
        
        // Generic Bot Patterns
        'bot' => 'Unknown Bot',
        'crawler' => 'Crawler',
        'spider' => 'Spider',
        'scraper' => 'Scraper',
        'fetch' => 'Fetcher',
        'curl' => 'cURL',
        'wget' => 'Wget',
        'python-requests' => 'Python Requests',
        'python-urllib' => 'Python URLLib',
        'java' => 'Java Client',
        'php' => 'PHP Client',
        'go-http-client' => 'Go HTTP Client',
        'axios' => 'Axios',
        'node-fetch' => 'Node Fetch',
        
        // Headless Browsers
        'headlesschrome' => 'Headless Chrome',
        'phantomjs' => 'PhantomJS',
        'puppeteer' => 'Puppeteer',
        'playwright' => 'Playwright',
        'selenium' => 'Selenium',
        
        // Feed Readers
        'feedly' => 'Feedly',
        'feedfetcher' => 'Feed Fetcher',
        'newsblur' => 'NewsBlur',
        
        // Preview Bots
        'embedly' => 'Embedly',
        'quora link preview' => 'Quora',
        'outbrain' => 'Outbrain',
        
        // Security Scanners
        'nessus' => 'Nessus Scanner',
        'nikto' => 'Nikto',
        'sqlmap' => 'SQLMap',
        'nmap' => 'Nmap',
        'masscan' => 'Masscan',
        
        // AI Bots
        'gptbot' => 'OpenAI GPTBot',
        'chatgpt' => 'ChatGPT',
        'claudebot' => 'Anthropic Claude',
        'anthropic' => 'Anthropic',
        'ccbot' => 'Common Crawl',
        'bytespider' => 'ByteDance',
        'petalbot' => 'Petal (Huawei)',
    ];

    /**
     * Check if user agent is a bot
     */
    public function isBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true; // No user agent = likely bot
        }

        $userAgentLower = strtolower($userAgent);

        foreach ($this->botPatterns as $pattern => $name) {
            if (str_contains($userAgentLower, $pattern)) {
                return true;
            }
        }

        // Additional heuristics
        if ($this->hasHeadlessBrowserIndicators($userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Get the bot name if detected
     */
    public function getBotName(?string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return 'Empty User-Agent';
        }

        $userAgentLower = strtolower($userAgent);

        // Check specific patterns first (more specific = higher priority)
        $specificPatterns = [
            'googlebot' => 'Googlebot',
            'bingbot' => 'Bingbot',
            'facebookexternalhit' => 'Facebook',
            'twitterbot' => 'Twitter',
            'gptbot' => 'OpenAI GPTBot',
            'claudebot' => 'Anthropic Claude',
            'semrushbot' => 'SEMrush',
            'ahrefsbot' => 'Ahrefs',
            'uptimerobot' => 'UptimeRobot',
        ];

        foreach ($specificPatterns as $pattern => $name) {
            if (str_contains($userAgentLower, $pattern)) {
                return $name;
            }
        }

        // Check generic patterns
        foreach ($this->botPatterns as $pattern => $name) {
            if (str_contains($userAgentLower, $pattern)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Detect headless browser indicators
     */
    protected function hasHeadlessBrowserIndicators(string $userAgent): bool
    {
        $indicators = [
            'HeadlessChrome',
            'PhantomJS',
            'Puppeteer',
            'Playwright',
            // Chrome without webdriver detection bypass
        ];

        foreach ($indicators as $indicator) {
            if (stripos($userAgent, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Full detection with both status and name
     */
    public function detect(?string $userAgent): array
    {
        $isBot = $this->isBot($userAgent);
        
        return [
            'is_bot' => $isBot,
            'bot_name' => $isBot ? $this->getBotName($userAgent) : null,
        ];
    }
}
