<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompileEBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:compile-ebook {--force : Zorla oluştur (20 hikaye sınırını takma)}';
    protected $description = 'Her 20 hikayeyi birleştirip bir E-Kitap (Volume) oluşturur.';

    public function handle(\App\Services\AIService $aiService)
    {
        $this->info('E-Kitap Derleyici Başlatılıyor...');

        // 1. Determine Start ID
        $lastBook = \App\Models\EBook::orderBy('volume_number', 'desc')->first();
        $startId = $lastBook ? ($lastBook->end_story_id + 1) : 1;
        $volume = $lastBook ? ($lastBook->volume_number + 1) : 1;

        $this->info("Hedef Cilt: Volume $volume");
        $this->info("Başlangıç Hikaye ID: $startId");

        // 2. Fetch Stories
        $stories = \App\Models\Story::where('id', '>=', $startId)
            ->where('durum', 'published') // Only published stories
            ->orderBy('id', 'asc')
            ->take(20)
            ->get();
        
        $count = $stories->count();
        $this->info("Bulunan Hikaye Sayısı: $count");

        if ($count < 20 && !$this->option('force')) {
            $this->warn("Yeterli hikaye yok (" . (20-$count) . " eksik). İşlem iptal edildi.");
            return;
        }

        if ($count === 0) {
            $this->error("Hiç hikaye bulunamadı!");
            return;
        }

        // 3. Prepare Content for AI
        $this->info('İçerik hazırlanıyor...');
        $storiesText = "";
        foreach ($stories as $story) {
            // Strip tags to save tokens but keep structure
            $text = strip_tags($story->metin);
            $storiesText .= "### BÖLÜM: {$story->baslik} (ID: {$story->id})\n{$text}\n\n";
        }

        // 4. Compile via AI
        $this->info('AI Baş Editör Romanı Kurguluyor (Bu işlem uzun sürebilir)...');
        try {
            $compiledHtml = $aiService->compileAnthology($storiesText, $volume);
            
            // Extract Title from <h1> tags using regex
            preg_match('/<h1>(.*?)<\/h1>/s', $compiledHtml, $matches);
            $title = $matches[1] ?? "Neo-Pera Chronicles: Volume $volume";
            $cleanTitle = strip_tags($title);
            $slug = \Illuminate\Support\Str::slug($cleanTitle) . "-vol-$volume";

        } catch (\Exception $e) {
            $this->error("AI Hatası: " . $e->getMessage());
            return;
        }

        // 5. Generate Cover Image
        $this->info('Kapak Görseli Tasarlanıyor...');
        $coverUrl = null;
        try {
            $coverPrompt = "Book Cover for Cyberpunk Novel named '$cleanTitle'. High quality, textless, cinematic, 8k, darker tone, serious art, digital painting";
            $remoteCover = $aiService->generateImage($coverPrompt);
            $localPath = "ebooks/cover_vol_{$volume}_" . time() . ".jpg";
            $coverUrl = $aiService->downloadImage($remoteCover, $localPath);
        } catch (\Exception $e) {
            $this->warn("Kapak oluşturulamadı: " . $e->getMessage());
        }

        // 6. Save to DB
        $ebook = \App\Models\EBook::create([
            'title' => $cleanTitle,
            'slug' => $slug,
            'volume_number' => $volume,
            'content' => $compiledHtml,
            'start_story_id' => $stories->first()->id,
            'end_story_id' => $stories->last()->id,
            'cover_image_url' => $coverUrl,
            'is_published' => true
        ]);

        $this->info("BAŞARILI: E-Kitap Oluşturuldu! ID: {$ebook->id} - {$ebook->title}");
    }
}
