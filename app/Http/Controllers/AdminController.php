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

    public function destroy(Story $story)
    {
        $story->delete();
        return redirect()->route('admin.stories.index')->with('success', 'Hikaye silindi.');
    }

    public function dashboard()
    {
        $stats = [
            'total_stories' => Story::count(),
            'published_stories' => Story::where('durum', 'published')->count(),
            'last_story' => Story::latest()->first(),
            'total_images' => \Illuminate\Support\Facades\File::exists(storage_path('app/public/stories')) 
                ? count(\Illuminate\Support\Facades\File::allFiles(storage_path('app/public/stories'))) 
                : 0
        ];

        return view('admin.dashboard', compact('stats'));
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

            $storyHtml = "";
            $coverImageUrl = null;
            $slug = \Illuminate\Support\Str::slug($data['baslik'] ?? 'story');
            $dateFolder = now()->format('Y-m-d');

            // 2. Process Scenes
            foreach ($data['scenes'] as $index => $scene) {
                $prompt = $scene['img_prompt'];
                $text = $scene['text'];
                
                $localUrl = "https://placehold.co/1280x720/050505/00ff00?text=Image+Error"; // Default fallback

                try {
                    // Generate Image URL
                    $remoteUrl = $this->aiService->generateImage($prompt);
                    
                    // Download Image Locally
                    $localPath = "stories/$dateFolder/{$slug}_{$index}.jpg";
                    $localUrl = $this->aiService->downloadImage($remoteUrl, $localPath);
                } catch (\Exception $e) {
                    // Log error but continue story generation
                    \Illuminate\Support\Facades\Log::error("Scene $index Image Failed: " . $e->getMessage());
                }

                // Determine Layout (Alternating)
                $layoutClass = ($index % 2 == 0) ? 'flex-row' : 'flex-row-reverse';

                // Append to Story HTML
                $storyHtml .= "<div class='scene-container mb-12 p-4 bg-gray-900/50 rounded-lg border border-gray-800'>";
                $storyHtml .= "  <div class='mb-4'><img src='$localUrl' alt='Scene $index' class='w-full rounded shadow-lg border-2 border-gray-800 hover:border-purple-500 transition duration-500'></div>";
                $storyHtml .= "  <div class='prose prose-invert prose-lg text-gray-300 font-sans leading-relaxed'><p>" . nl2br(e($text)) . "</p></div>";
                $storyHtml .= "</div>";

                // Use the first image as cover
                if ($index === 0) {
                    $coverImageUrl = $localUrl;
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
                'meta' => ($data['meta_baslik'] ?? '') . ' | ' . ($data['meta_aciklama'] ?? ''),
                'etiketler' => $data['etiketler'] ?? [],
                'sosyal_ozet' => $data['sosyal_ozet'] ?? '',
                'gorsel_prompt' => json_encode(array_column($data['scenes'], 'img_prompt')), // Store all prompts
            ];

            $story = Story::create($storyData);
            
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

        try {
            $remoteUrl = $this->aiService->generateImage($prompt);
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
                'meta' => ($data['meta_baslik'] ?? '') . ' | ' . ($data['meta_aciklama'] ?? ''),
                'etiketler' => $data['etiketler'] ?? [],
                'sosyal_ozet' => $data['sosyal_ozet'] ?? '',
                'gorsel_prompt' => json_encode(array_column($scenes, 'img_prompt')),
            ];

            $story = Story::create($storyData);
            
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
}
