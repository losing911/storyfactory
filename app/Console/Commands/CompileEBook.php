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

        // 3. Process in Chunks (Avoid Timeouts)
        $this->info("Toplam {$count} hikaye 5'erli paketler halinde işlenecek...");
        
        $chunks = $stories->chunk(5);
        $totalParts = $chunks->count();
        $compiledHtml = "";
        $part = 1;

        foreach ($chunks as $chunk) {
            $this->info("Parça İşleniyor: $part / $totalParts");
            
            // Prepare text for this chunk
            $chunkText = "";
            foreach ($chunk as $story) {
                $text = strip_tags($story->metin);
                $chunkText .= "### BÖLÜM: {$story->baslik} (ID: {$story->id})\n{$text}\n\n";
            }

            try {
                // Generate Illustration for this Part
                $partTitle = $chunk->first()->baslik;
                $this->info("Parça Görseli Tasarlanıyor: $partTitle");
                
                $imgPrompt = "Anime style illustration for cyberpunk story chapter: $partTitle. Action scene or atmospheric city shot, cel shaded, high quality, no text.";
                $remoteImg = $aiService->generateImage($imgPrompt);
                $localImgPath = "ebooks/vol_{$volume}_part_{$part}_" . time() . ".jpg";
                $localImgUrl = $aiService->downloadImage($remoteImg, $localImgPath);
                
                // Compile Partial HTML
                $partialHtml = $aiService->compileAnthology($chunkText, $volume, $part, $totalParts);
                
                // Construct HTML with Image
                $finalPartHtml = "<div class='volume-part' id='part-{$part}'>";
                $finalPartHtml .= "<div class='part-illustration' style='text-align:center; margin-bottom:2rem;'><img src='/" . $localImgPath . "' style='max-width:100%; border-radius:4px; border:1px solid #333;' alt='Chapter Art'></div>";
                $finalPartHtml .= $partialHtml;
                $finalPartHtml .= "</div><hr class='part-divider'>";
                
                $compiledHtml .= $finalPartHtml;
                
                $this->info("Parça $part Tamamlandı (Görsel Eklendi).");
            } catch (\Exception $e) {
                $this->error("Parça $part Hatası: " . $e->getMessage());
                $this->warn("Devam ediliyor...");
            }

            $part++;
            // Cool down to avoid rate limits
            sleep(5);
        }

        // Extract Title from First Part
        preg_match('/<h1>(.*?)<\/h1>/s', $compiledHtml, $matches);
        $title = $matches[1] ?? "Neo-Pera Chronicles: Volume $volume";
        $cleanTitle = strip_tags($title);
        $slug = \Illuminate\Support\Str::slug($cleanTitle) . "-vol-$volume";

        // 5. Generate Cover Image
        $this->info('Kapak Görseli Tasarlanıyor...');
        $coverUrl = null;
        try {
            $coverPrompt = "Anime Style Book Cover for Cyberpunk Novel named '$cleanTitle'. Studio Ghibli meets Akira, detailed line art, cel shaded, no text, cinematic composition, 8k";
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
