<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class LogVisitorActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // 1. Exclude Bots/Crawlers (Simple Check)
            $userAgent = $request->header('User-Agent');
            if (Str::contains(strtolower($userAgent), ['bot', 'crawler', 'spider', 'uptime', 'monitor'])) {
                return $next($request);
            }

            // 2. Exclude Asset/Admin Routes
            if ($request->is('admin*', 'api*', 'storage*', '*.png', '*.jpg', '*.css', '*.js', 'favicon.ico')) {
                return $next($request);
            }

            // 3. Get or Set Visitor ID
            $visitorId = $request->cookie('netrunner_id');
            $cookieToQueue = null;

            if (!$visitorId) {
                $visitorId = (string) Str::uuid();
                // Queue cookie for 365 days
                $cookieToQueue = Cookie::make('netrunner_id', $visitorId, 60 * 24 * 365);
            }

            // 4. Async/Defer Logging (Ideally use a Job, but DB insert is fast enough for MVP)
            // Detect Device
            $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent ?? '');
            $device = $isMobile ? 'mobile' : 'desktop';

            DB::table('analytics_logs')->insert([
                'visitor_id' => $visitorId,
                'ip_address' => $request->ip(),
                'url' => $request->path(),
                'referrer' => $request->header('referer'),
                'device_type' => $device,
                'user_agent' => substr($userAgent ?? '', 0, 255),
                'created_at' => now(),
            ]);

            $response = $next($request);

            // Attach cookie if new
            if ($cookieToQueue) {
                $response->withCookie($cookieToQueue);
            }

            return $response;

        } catch (\Exception $e) {
            // Failsafe: Continue request even if analytics breaks
            // Log::error($e->getMessage()); // Optional logging
            return $next($request);
        }
    }
}
