<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceWwwRedirect
{
    /**
     * Handle an incoming request.
     * 
     * Redirects all non-www requests to www (or vice versa)
     * with 301 permanent redirect for SEO.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for local development
        if (app()->environment('local')) {
            return $next($request);
        }

        $host = $request->getHost();
        
        // OPTION 1: Force WWW (recommended for SEO consistency)
        // Uncomment if you want www.anxipunk.icu to be canonical
        if (!str_starts_with($host, 'www.')) {
            $newUrl = $request->getScheme() . '://www.' . $host . $request->getRequestUri();
            return redirect($newUrl, 301);
        }

        // OPTION 2: Force Non-WWW
        // Uncomment if you want anxipunk.icu (without www) to be canonical
        // if (str_starts_with($host, 'www.')) {
        //     $newHost = substr($host, 4);
        //     $newUrl = $request->getScheme() . '://' . $newHost . $request->getRequestUri();
        //     return redirect($newUrl, 301);
        // }

        return $next($request);
    }
}
