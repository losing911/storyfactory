<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use App\Services\BotDetector;

class LogVisitorActivity
{
    protected BotDetector $botDetector;

    public function __construct(BotDetector $botDetector)
    {
        $this->botDetector = $botDetector;
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            // 1. Exclude Asset/Admin Routes (but NOT bots - we want to track them)
            if ($request->is('admin*', 'api*', 'storage*', '*.png', '*.jpg', '*.css', '*.js', 'favicon.ico')) {
                return $next($request);
            }

            // 2. Detect Bot
            $userAgent = $request->header('User-Agent');
            $botInfo = $this->botDetector->detect($userAgent);

            // 3. Get or Set Visitor ID (Identify New vs Returning)
            $visitorId = $request->cookie('netrunner_id');
            $cookieToQueue = null;
            $attributionCookie = null;
            $isNewVisitor = false;

            if (!$visitorId) {
                $visitorId = (string) Str::uuid();
                // Queue cookie for 365 days
                $cookieToQueue = Cookie::make('netrunner_id', $visitorId, 60 * 24 * 365);
                $isNewVisitor = true;
            }

            // 4. Handle UTM & Attribution
            // Check URL for UTMs
            $utmSource = $request->query('utm_source');
            $utmMedium = $request->query('utm_medium');
            $utmCampaign = $request->query('utm_campaign');
            
            // If found in URL, update Attribution Cookie
            if ($utmSource || $utmMedium || $utmCampaign) {
                $attributionData = json_encode([
                    'source' => $utmSource,
                    'medium' => $utmMedium,
                    'campaign' => $utmCampaign
                ]);
                $attributionCookie = Cookie::make('traffic_attribution', $attributionData, 60 * 24 * 30); // 30 Days attribution
            } else {
                // If not in URL, try to recover from Cookie
                $savedAttribution = json_decode($request->cookie('traffic_attribution'), true);
                if ($savedAttribution) {
                    $utmSource = $savedAttribution['source'] ?? null;
                    $utmMedium = $savedAttribution['medium'] ?? null;
                    $utmCampaign = $savedAttribution['campaign'] ?? null;
                }
            }

            // 5. Async/Defer Logging (Ideally use a Job, but DB insert is fast enough for MVP)
            // Detect Device
            $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent ?? '');
            $device = $isMobile ? 'mobile' : 'desktop';

            DB::table('analytics_logs')->insert([
                'visitor_id' => $visitorId,
                'is_bot' => $botInfo['is_bot'],
                'bot_name' => $botInfo['bot_name'],
                'is_new_visitor' => $isNewVisitor,
                'ip_address' => $request->ip(),
                'url' => $request->path(),
                'referrer' => $request->header('referer'),
                'device_type' => $device,
                'user_agent' => substr($userAgent ?? '', 0, 255),
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'created_at' => now(),
            ]);

            $response = $next($request);

            // Attach cookies if needed
            if ($cookieToQueue) {
                $response->withCookie($cookieToQueue);
            }
            if ($attributionCookie) {
                $response->withCookie($attributionCookie);
            }

            return $response;

        } catch (\Exception $e) {
            // Failsafe: Continue request even if analytics breaks
            // Log::error($e->getMessage()); // Optional logging
            return $next($request);
        }
    }
}
