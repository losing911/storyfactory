<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\AIService;
use App\Services\SocialPosterService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $aiService;
    protected $socialPoster;

    public function __construct(AIService $aiService, SocialPosterService $socialPoster)
    {
        $this->aiService = $aiService;
        $this->socialPoster = $socialPoster;
    }

    public function index()
    {
        $stories = Story::latest()->paginate(10);
        return view('admin.index', compact('stories'));
    }

    public function dashboard()
    {
        try {
            // Stats - Separate Real vs Bot Traffic
            $allLogs = \Illuminate\Support\Facades\DB::table('analytics_logs');
            $realLogs = \Illuminate\Support\Facades\DB::table('analytics_logs')->where('is_bot', false);
            $botLogs = \Illuminate\Support\Facades\DB::table('analytics_logs')->where('is_bot', true);

            $stats = [
                'total_stories' => Story::count(),
                // Real visitor stats (what matters)
                'total_views' => (clone $realLogs)->count(),
                'unique_visitors' => (clone $realLogs)->distinct('visitor_id')->count('visitor_id'),
                // Bot stats (for transparency)
                'bot_views' => (clone $botLogs)->count(),
                'unique_bots' => (clone $botLogs)->distinct('bot_name')->count('bot_name'),
            ];

            // Top Bots Detected
            $topBots = \Illuminate\Support\Facades\DB::table('analytics_logs')
                ->select('bot_name', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->where('is_bot', true)
                ->whereNotNull('bot_name')
                ->groupBy('bot_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Latest AI Insight
            $insight = \Illuminate\Support\Facades\DB::table('analytics_insights')->orderBy('report_date', 'desc')->first();

            // Recent Logs (only real visitors)
            $recentLogs = \Illuminate\Support\Facades\DB::table('analytics_logs')
                ->where('is_bot', false)
                ->latest()
                ->take(10)
                ->get();

            // Recent E-Books
            $ebooks = \App\Models\EBook::latest()->take(5)->get();

            // Traffic Sources Analysis (only real visitors)
            $logs = \Illuminate\Support\Facades\DB::table('analytics_logs')
                ->where('is_bot', false)
                ->select('referrer', 'is_new_visitor', 'utm_source', 'utm_medium')
                ->get();

            $trafficSources = ['Search' => 0, 'Social' => 0, 'Direct' => 0, 'Other' => 0];
            $trafficDetails = ['Search' => [], 'Social' => [], 'Other' => []];
            
            // Visitor Stats (only real visitors)
            $visitorStats = [
                'new' => $logs->where('is_new_visitor', 1)->count(),
                'returning' => $logs->where('is_new_visitor', 0)->count(),
            ];

            foreach ($logs as $log) {
                $ref = strtolower($log->referrer);
                $host = parse_url($ref, PHP_URL_HOST);
                
                // 1. Check UTM (Priority Attribution)
                if ($log->utm_source) {
                    $source = 'Other'; // Default bucket for campaigns unless matched otherwise
                    // You could add a 'Campaign' category, but let's stick to base 4 for now.
                    // Or map UTM sources to Social/Search if they match known keywords
                }

                if (empty($ref)) {
                    $trafficSources['Direct']++;
                } elseif (str_contains($ref, 'google.') || str_contains($ref, 'bing.') || str_contains($ref, 'yahoo.') || str_contains($ref, 'duckduckgo.')) {
                    $trafficSources['Search']++;
                    
                    // Detailed Search Analysis (Engine + Keyword attempt)
                    $engine = 'Google';
                    if (str_contains($ref, 'bing.')) $engine = 'Bing';
                    elseif (str_contains($ref, 'yahoo.')) $engine = 'Yahoo';
                    elseif (str_contains($ref, 'duckduckgo.')) $engine = 'DuckDuckGo';

                    parse_str(parse_url($ref, PHP_URL_QUERY), $query);
                    $keyword = $query['q'] ?? ($query['p'] ?? ($query['query'] ?? null));
                    
                    $key = $keyword ? "$engine ($keyword)" : $engine;
                    $trafficDetails['Search'][$key] = ($trafficDetails['Search'][$key] ?? 0) + 1;

                } elseif (str_contains($ref, 'facebook.') || str_contains($ref, 'twitter.') || str_contains($ref, 't.co') || str_contains($ref, 'instagram.') || str_contains($ref, 'reddit.') || str_contains($ref, 'linkedin.') || str_contains($ref, 'youtube.')) {
                    $trafficSources['Social']++;

                    // Detailed Social Analysis
                    $platform = 'Social';
                    if (str_contains($ref, 'facebook.')) $platform = 'Facebook';
                    elseif (str_contains($ref, 'twitter.') || str_contains($ref, 't.co')) $platform = 'Twitter';
                    elseif (str_contains($ref, 'instagram.')) $platform = 'Instagram';
                    elseif (str_contains($ref, 'reddit.')) $platform = 'Reddit';
                    elseif (str_contains($ref, 'linkedin.')) $platform = 'LinkedIn';
                    elseif (str_contains($ref, 'youtube.')) $platform = 'YouTube';

                    $trafficDetails['Social'][$platform] = ($trafficDetails['Social'][$platform] ?? 0) + 1;

                } else {
                    $trafficSources['Other']++;
                    // Detailed Other Analysis (Domain)
                    if ($host) {
                        $trafficDetails['Other'][$host] = ($trafficDetails['Other'][$host] ?? 0) + 1;
                    }
                }
            }

            // Calculate Percentages
            $totalTraffic = array_sum($trafficSources);
            $trafficPercentages = [];
            if ($totalTraffic > 0) {
                foreach($trafficSources as $key => $val) {
                    $trafficPercentages[$key] = round(($val / $totalTraffic) * 100, 1);
                }
            } else {
                 $trafficPercentages = ['Search' => 0, 'Social' => 0, 'Direct' => 0, 'Other' => 0];
            }
            
            // Sort Details by Count DESC
            arsort($trafficDetails['Search']);
            arsort($trafficDetails['Social']);
            arsort($trafficDetails['Other']);

            return view('admin.dashboard', compact('stats', 'insight', 'recentLogs', 'ebooks', 'trafficSources', 'trafficPercentages', 'trafficDetails', 'visitorStats', 'topBots'));
        } catch (\Exception $e) {
            return response()->make("
                <div style='background:#000; color:#ff0000; padding:20px; font-family:monospace; border:1px solid #ff0000; margin:20px;'>
                    <h1>SYSTEM_FAILURE: ADMIN_DASHBOARD</h1>
                    <p><strong>Error:</strong> " . $e->getMessage() . "</p>
                    <p><strong>Location:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>
                    <hr style='border:1px solid #333'>
                    <small>Check database migrations and model definitions.</small>
                </div>
            ", 500);
        }
    }

    public function create()
    {
        return view('admin.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'baslik' => 'required|string|max:255',
            'metin' => 'required',
            'konu' => 'required',
            'yayin_tarihi' => 'required|date',
            'durum' => 'required',
        ]);

        $data = $request->all();
        $data['slug'] = \Illuminate\Support\Str::slug($data['baslik']);
        
        Story::create($data);

        return redirect()->route('admin.stories.index')->with('success', 'Hikaye oluşturuldu.');
    }

    public function edit(Story $story)
    {
        return view('admin.form', compact('story'));
    }

    public function update(Request $request, Story $story)
    {
         $validated = $request->validate([
            'baslik' => 'required|string|max:255',
            'metin' => 'required',
            'konu' => 'required',
            'yayin_tarihi' => 'required|date',
            'durum' => 'required',
        ]);

        $story->update($request->all());

        return redirect()->route('admin.stories.index')->with('success', 'Hikaye güncellendi.');
    }

    public function publish(Story $story)
    {
        $story->update(['durum' => 'published']);
        return redirect()->back()->with('success', 'Hikaye yayınlandı!');
    }

    public function destroy(Story $story)
    {
        $story->delete();        
        return redirect()->route('admin.stories.index')->with('error', 'Hikaye bulunamadı.');
    }
    
    /**
     * SEO Tools Page
     */
    public function seoTools()
    {
        return view('admin.seo-tools');
    }
    
    /**
     * Generate Sitemap
     */
    public function generateSitemap()
    {
        try {
            \Artisan::call('sitemap:generate');
            return redirect()->route('admin.seo.tools')->with('success', 'Sitemap başarıyla oluşturuldu!');
        } catch (\Exception $e) {
            return redirect()->route('admin.seo.tools')->with('error', 'Sitemap oluşturulamadı: ' . $e->getMessage());
        }
    }

    public function createAI()
    {
        return view('admin.create_ai');
    }

    public function generateAI(Request $request)
    {
        set_time_limit(0); // Unlimited time for heavy AI processing
        $topic = $request->input('topic');
        
        try {
            // 1. Generate Story Structure (JSON)
            $data = $this->aiService->generateFullStory($topic);
            
            // Check if 'scenes' exists, otherwise handle legacy/error format
            if (!isset($data['scenes']) || !is_array($data['scenes'])) {
                throw new \Exception("AI yanıtı beklenen 'scenes' formatında değil.");
            }


            // 2. Process Scenes (Comic Format - Multiple Images per Scene)
            $storyHtml = "";
            $coverImageUrl = null;
            $slug = \Illuminate\Support\Str::slug($data['baslik'] ?? 'story');
            $dateFolder = now()->format('Y-m-d');
            
            foreach ($data['scenes'] as $sceneIndex => $scene) {
                // Handle both old format (img_prompt) and new format (img_prompts array)
                $prompts = isset($scene['img_prompts']) && is_array($scene['img_prompts']) 
                    ? $scene['img_prompts'] 
                    : (isset($scene['img_prompt']) ? [$scene['img_prompt']] : []);
                
                $text = $scene['text'] ?? '';
                $sceneImages = [];
                
                // Get Visual Constraints
                $visualConstraints = $data['meta_visual_prompts'] ?? null;
                
                // Generate multiple images per scene
                foreach ($prompts as $promptIndex => $prompt) {
                    if (empty($prompt)) continue;
                    
                    $fallbackUrl = "https://placehold.co/1280x720/050505/00ff00?text=Panel+" . ($promptIndex + 1);
                    
                    try {
                        $remoteUrl = $this->aiService->generateImage($prompt, $visualConstraints);
                        $localPath = "stories/$dateFolder/{$slug}_s{$sceneIndex}_p{$promptIndex}.jpg";
                        $localUrl = $this->aiService->downloadImage($remoteUrl, $localPath);
                        $sceneImages[] = $localUrl;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Scene $sceneIndex Panel $promptIndex failed: " . $e->getMessage());
                        $sceneImages[] = $fallbackUrl;
                    }
                }
                
                // Build HTML with Comic Panel Layout
                $imageCount = count($sceneImages);
                $storyHtml .= "<div class='scene-container mb-16 p-6 bg-gray-900/50 rounded-lg border border-gray-800 hover:border-neon-pink transition duration-300'>";
                
                // Panel Grid Layout
                if ($imageCount >= 3) {
                    // 3+ panels: Main panel + smaller panels
                    $storyHtml .= "<div class='grid grid-cols-2 gap-4 mb-6'>";
                    $storyHtml .= "  <div class='col-span-2'><img src='{$sceneImages[0]}' alt='Panel 1' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500' loading='lazy'></div>";
                    if (isset($sceneImages[1])) {
                        $storyHtml .= "  <div><img src='{$sceneImages[1]}' alt='Panel 2' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-cyan-500 transition duration-500' loading='lazy'></div>";
                    }
                    if (isset($sceneImages[2])) {
                        $storyHtml .= "  <div><img src='{$sceneImages[2]}' alt='Panel 3' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-pink-500 transition duration-500' loading='lazy'></div>";
                    }
                    $storyHtml .= "</div>";
                } elseif ($imageCount == 2) {
                    // 2 panels: Side by side
                    $storyHtml .= "<div class='grid grid-cols-2 gap-4 mb-6'>";
                    $storyHtml .= "  <div><img src='{$sceneImages[0]}' alt='Panel 1' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500' loading='lazy'></div>";
                    $storyHtml .= "  <div><img src='{$sceneImages[1]}' alt='Panel 2' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-cyan-500 transition duration-500' loading='lazy'></div>";
                    $storyHtml .= "</div>";
                } elseif ($imageCount == 1) {
                    // 1 panel: Full width
                    $storyHtml .= "<div class='mb-6'><img src='{$sceneImages[0]}' alt='Scene Panel' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500' loading='lazy'></div>";
                }
                
                // Text (Comic dialogue/narration)
                $storyHtml .= "  <div class='prose prose-invert prose-lg text-gray-300 font-sans leading-relaxed'>";
                $storyHtml .= "    <p>" . nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) . "</p>";
                $storyHtml .= "  </div>";
                $storyHtml .= "</div>";
                
                // First scene's first image = cover
                if ($sceneIndex === 0 && !empty($sceneImages)) {
                    $coverImageUrl = $sceneImages[0];
                }
            }


            // Merge Data
            $storyData = [
                'baslik' => $data['baslik'],
                'slug' => $slug, // Already generated above for folder name
                'metin' => $storyHtml, // Now contains HTML with images
                'gorsel_url' => $coverImageUrl,
                'yayin_tarihi' => now(),
                'durum' => 'published',
                'konu' => $topic ?? 'AI Generated',
                'mood' => $data['mood'] ?? null,
                'meta' => ($data['meta_baslik'] ?? '') . ' | ' . ($data['meta_aciklama'] ?? ''),
                'etiketler' => $data['etiketler'] ?? [],
                'sosyal_ozet' => $data['sosyal_ozet'] ?? '',
                'gorsel_prompt' => json_encode(array_column($data['scenes'], 'img_prompt')), // Store all prompts
            ];

            $story = Story::create($storyData);

            // Process New Lore (Auto-Extraction)
            if (!empty($data['new_lore']) && is_array($data['new_lore'])) {
                foreach ($data['new_lore'] as $loreItem) {
                    try {
                        if (empty($loreItem['title']) || empty($loreItem['type'])) continue;
                        $loreSlug = \Illuminate\Support\Str::slug($loreItem['title']);
                        
                        if (!\App\Models\LoreEntry::where('slug', $loreSlug)->exists()) {
                            \App\Models\LoreEntry::create([
                                'title' => $loreItem['title'],
                                'slug' => $loreSlug,
                                'type' => strtolower($loreItem['type']) === 'location' ? 'city' : strtolower($loreItem['type']),
                                'description' => $loreItem['description'] ?? 'AI tarafından keşfedildi.',
                                'visual_prompt' => $loreItem['visual_prompt'] ?? null,
                                'is_active' => true,
                            ]);
                        }
                    } catch (\Exception $e) {
                         // Ignore map errors
                    }
                }
            }
            
            // Simulate Social Posting
            $this->socialPoster->postToSocialMedia($story);

            return redirect()->route('admin.stories.index')->with('success', 'Çizgi Roman Hikaye üretildi ve yayınlandı! (Görseller Sunucuya İndirildi)');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'AI Hatası: ' . $e->getMessage()]);
        }
    }

    // --- Chunked Generation Methods ---

    public function generateStoryStep(Request $request)
    {
        set_time_limit(120); 
        $topic = $request->input('topic');
        
        try {
            $data = $this->aiService->generateFullStory($topic);
            
            if (!isset($data['scenes']) || !is_array($data['scenes'])) {
                throw new \Exception("AI structure invalid.");
            }

            // Prepare basic data
            $slug = \Illuminate\Support\Str::slug($data['baslik'] ?? 'story-' . rand(1000,9999));
            $dateFolder = now()->format('Y-m-d');
            
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'slug' => $slug,
                'dateFolder' => $dateFolder
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function generateImageStep(Request $request)
    {
        set_time_limit(120);
        $prompt = $request->input('prompt');
        $slug = $request->input('slug');
        $index = $request->input('index');
        $dateFolder = $request->input('dateFolder');
        $visualConstraints = $request->input('visual_constraints'); // New Input

        try {
            $remoteUrl = $this->aiService->generateImage($prompt, $visualConstraints);
            $localPath = "stories/$dateFolder/{$slug}_{$index}.jpg";
            $localUrl = $this->aiService->downloadImage($remoteUrl, $localPath);

            return response()->json([
                'status' => 'success',
                'localUrl' => $localUrl,
                'index' => $index
            ]);

        } catch (\Exception $e) {
             // Fallback image
             $fallback = "https://placehold.co/1280x720/050505/00ff00?text=Image+Error+Scene+$index";
             return response()->json([
                'status' => 'success', // Return success so chain doesn't break
                'localUrl' => $fallback, 
                'index' => $index,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function storeStoryStep(Request $request)
    {
        try {
            $data = $request->all();
            
            // Reconstruct HTML from arrays
            $storyHtml = "";
            $scenes = $data['scenes'] ?? [];
            $images = $data['images'] ?? []; // Array of URLs keyed by index
            $coverImageUrl = $images[0] ?? null;

            foreach ($scenes as $index => $scene) {
                $imageUrl = $images[$index] ?? "https://placehold.co/1280x720/050505/00ff00?text=Missing+Image";
                $text = $scene['text'];
                
                $storyHtml .= "<div class='scene-container mb-12 p-4 bg-gray-900/50 rounded-lg border border-gray-800'>";
                $storyHtml .= "  <div class='mb-4'><img src='$imageUrl' alt='Scene $index' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500'></div>";
                $storyHtml .= "  <div class='prose prose-invert prose-lg text-gray-300 font-sans leading-relaxed'><p>" . nl2br(e($text)) . "</p></div>";
                $storyHtml .= "</div>";
            }

            $storyData = [
                'baslik' => $data['baslik'],
                'slug' => $data['slug'],
                'metin' => $storyHtml,
                'gorsel_url' => $coverImageUrl,
                'yayin_tarihi' => now(),
                'durum' => 'published',
                'konu' => $data['konu'] ?? 'AI Generated',
                'mood' => $data['mood'] ?? null,
                'meta' => \Illuminate\Support\Str::limit(($data['meta_baslik'] ?? '') . ' | ' . ($data['meta_aciklama'] ?? ''), 250),
                'etiketler' => $data['etiketler'] ?? [],
                'sosyal_ozet' => \Illuminate\Support\Str::limit($data['sosyal_ozet'] ?? '', 250),
                'gorsel_prompt' => json_encode(array_column($scenes, 'img_prompt')),
            ];

            $story = Story::create($storyData);

             // Process New Lore (Auto-Extraction)
             // Note: data['new_lore'] comes from frontend -> backend. 
             // Since we didn't pass it openly in frontend JS yet, we might miss it in async mode unless we update frontend too.
             // But 'data' here is request->all(), which comes from frontend. 
             // We need to ensure 'new_lore' is passed from `generateStoryStep` -> frontend -> `storeStoryStep`.
             
             // Currently: `generateStoryStep` returns `data` (which includes `new_lore`).
             // Frontend `create_ai.blade.php` stores `storyData` in JS.
             // Frontend sends `finalPayload` in `storeStoryStep`.
             // `finalPayload` includes `...storyData`.
             // So `new_lore` IS passed automatically! Logic below is valid.

            if (!empty($data['new_lore']) && is_array($data['new_lore'])) {
                foreach ($data['new_lore'] as $loreItem) {
                    try {
                        if (empty($loreItem['title']) || empty($loreItem['type'])) continue;
                        $loreSlug = \Illuminate\Support\Str::slug($loreItem['title']);
                        
                        if (!\App\Models\LoreEntry::where('slug', $loreSlug)->exists()) {
                            \App\Models\LoreEntry::create([
                                'title' => $loreItem['title'],
                                'slug' => $loreSlug,
                                'type' => strtolower($loreItem['type']) === 'location' ? 'city' : strtolower($loreItem['type']),
                                'description' => $loreItem['description'] ?? 'AI tarafından keşfedildi.',
                                'visual_prompt' => $loreItem['visual_prompt'] ?? null,
                                'is_active' => true,
                            ]);
                        }
                    } catch (\Exception $e) { }
                }
            }
            
            // Async Social Posting (Optional, could be queued)
            try {
                $this->socialPoster->postToSocialMedia($story);
            } catch (\Exception $e) {
                // Ignore social errors
            }

            return response()->json([
                'status' => 'success',
                'redirect' => route('admin.stories.index')
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Profile Management
    public function editProfile()
    {
        return view('admin.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function regenerateImages(Story $story)
    {
        set_time_limit(300); // 5 minutes
        
        try {
            $prompts = json_decode($story->gorsel_prompt, true);
            
            if (!$prompts || !is_array($prompts)) {
                throw new \Exception("Bu hikaye için kayıtlı görsel promtları bulunamadı. (Eski sürüm olabilir)");
            }

            $dateFolder = now()->format('Y-m-d');
            $slug = $story->slug;
            $updatedHtml = $story->metin;
            $newCoverUrl = null;

            foreach ($prompts as $index => $prompt) {
                // Rate Limit Protection
                if($index > 0) sleep(4); 

                // Generate New Image
                $remoteUrl = $this->aiService->generateImage($prompt);
                
                // Save with versioning to bust cache
                $version = time();
                $localPath = "stories/$dateFolder/{$slug}_{$index}_v{$version}.jpg";
                $localUrl = $this->aiService->downloadImage($remoteUrl, $localPath);

                // Replace in HTML
                // Regex looks for: src='old_url' ... alt='Scene index'
                // We construct a regex to match the img tag specifically for this index
                // Note: The alt tag is "Scene $index"
                
                // Simple string replacement might be risky if duplicated, but alt='Scene X' is unique per scene.
                // Let's use Regex to find the <img> tag with that alt.
                
                $pattern = '/<img[^>]+alt=[\'"]Scene ' . $index . '[\'"][^>]*>/i';
                
                if (preg_match($pattern, $updatedHtml, $matches)) {
                    $oldImgTag = $matches[0];
                    // Replace src attribute in this specific tag
                    $newImgTag = preg_replace('/src=[\'"][^\'"]+[\'"]/i', "src='$localUrl'", $oldImgTag);
                    $updatedHtml = str_replace($oldImgTag, $newImgTag, $updatedHtml);
                }

                if ($index === 0) {
                    $newCoverUrl = $localUrl;
                }
            }

            // Update Story
            $story->metin = $updatedHtml;
            if ($newCoverUrl) $story->gorsel_url = $newCoverUrl;
            $story->save();

            return redirect()->back()->with('success', 'Tüm görseller başarıyla yeniden üretildi ve güncellendi!');

        } catch (\Exception $e) {
             return redirect()->back()->with('error', 'Görsel Yenileme Hatası: ' . $e->getMessage());
        }
    }

    public function regenerateImageChunk(Request $request, $id)
    {
        // AJAX Method to regenerate a single image
        set_time_limit(120);
        
        // Find manually because Route Binding expects SLUG but we pass ID
        $story = Story::findOrFail($id);

        $index = $request->input('index');
        $prompts = json_decode($story->gorsel_prompt, true);
        $prompt = $prompts[$index] ?? null;

        if (!$prompt) return response()->json(['status' => 'error', 'message' => 'Prompt not found'], 404);

        try {
            // Generate
            $remoteUrl = $this->aiService->generateImage($prompt);
            
            // Save
            $dateFolder = now()->format('Y-m-d');
            $slug = $story->slug;
            $version = time();
            $localPath = "stories/$dateFolder/{$slug}_{$index}_v{$version}.jpg";
            $localUrl = $this->aiService->downloadImage($remoteUrl, $localPath);

            // Update HTML (Atomic Update)
            // We need to fetch FRESH story content because other chunks might have updated it
            $currentStory = Story::find($story->id);
            $updatedHtml = $currentStory->metin;
            
            // Regex to find the img tag for this index
            $pattern = '/<img[^>]+alt=[\'"]Scene ' . $index . '[\'"][^>]*>/i';
            if (preg_match($pattern, $updatedHtml, $matches)) {
                $oldImgTag = $matches[0];
                $newImgTag = preg_replace('/src=[\'"][^\'"]+[\'"]/i', "src='$localUrl'", $oldImgTag);
                $updatedHtml = str_replace($oldImgTag, $newImgTag, $updatedHtml);
            }

            $currentStory->metin = $updatedHtml;
            if ($index == 0) $currentStory->gorsel_url = $localUrl;
            $currentStory->save();

            return response()->json([
                'status' => 'success',
                'index' => $index,
                'url' => $localUrl
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
