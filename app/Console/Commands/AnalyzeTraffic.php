<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\AIService;
use Carbon\Carbon;

class AnalyzeTraffic extends Command
{
    protected $signature = 'app:analyze-traffic';
    protected $description = 'Analyzes the last 24h of traffic logs using AI to generate insights and ad strategies.';

    public function handle(AIService $aiService)
    {
        $this->info('Gathering Intelligence...');
        
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        // 1. Aggregate Data
        $totalVisits = DB::table('analytics_logs')->whereBetween('created_at', [$yesterday, $today])->count();
        $uniqueVisitors = DB::table('analytics_logs')->whereBetween('created_at', [$yesterday, $today])->distinct('visitor_id')->count('visitor_id');
        
        $topPages = DB::table('analytics_logs')
            ->select('url', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$yesterday, $today])
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
            
        $deviceStats = DB::table('analytics_logs')
            ->select('device_type', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$yesterday, $today])
            ->groupBy('device_type')
            ->pluck('total', 'device_type');

        // Referrers
        $referrers = DB::table('analytics_logs')
            ->select('referrer', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$yesterday, $today])
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($totalVisits === 0) {
            $this->warn('No data to analyze.');
            return;
        }

        // 2. Prepare Prompt for AI
        $prompt = "Sen Dijital Strateji ve Pazarlama Uzmanısın. Aşağıdaki web sitesi trafik verilerini analiz et ve Anxipunk.art (Cyberpunk Hikaye Blogu) için strateji geliştir.\n\n";
        $prompt .= "--- VERİLER (Son 24 Saat) ---\n";
        $prompt .= "- Tekil Ziyaretçi: $uniqueVisitors\n";
        $prompt .= "- Toplam Gösterim: $totalVisits\n";
        $prompt .= "- Cihaz Dağılımı: Mobile (".($deviceStats['mobile']??0)."), Desktop (".($deviceStats['desktop']??0).")\n";
        $prompt .= "- En Çok Okunan Sayfalar:\n" . $topPages->map(fn($p) => "  * {$p->url} ({$p->total})")->implode("\n") . "\n";
        $prompt .= "- Trafik Kaynakları:\n" . $referrers->map(fn($r) => "  * {$r->referrer} ({$r->total})")->implode("\n") . "\n\n";
        
        $prompt .= "--- GÖREV ---\n";
        $prompt .= "1. **İçerik Önerisi**: Okuyucu davranışına göre yarın hangi türde (Aksiyon, Dram, Polisiye vb.) 3 hikaye konusu önerirsin?\n";
        $prompt .= "2. **Reklam Stratejisi (ÖNEMLİ)**: Eğer yarın reklam vereceksek;\n";
        $prompt .= "   - Hangi platformu (Twitter / Instagram / Google) seçmeliyiz?\n";
        $prompt .= "   - Hedef kitlemiz kim olmalı (Örn: 'Gece kuşları', 'Mobil oyun severler')?\n";
        $prompt .= "   - Reklam metni (Caption) ne olmalı?\n\n";
        
        $prompt .= "Yanıtı Markdown formatında, şık ve okunabilir bir rapor olarak ver.";

        $this->info('AI Düşünüyor...');
        
        // Use DeepSeek (OpenRouter)
        try {
            $insight = $aiService->generateRawWithOpenRouter($prompt, 'nex-agi/deepseek-v3.1-nex-n1:free');
            
            // 3. Save to DB
            DB::table('analytics_insights')->updateOrInsert(
                ['report_date' => $yesterday->toDateString()],
                [
                    'summary_text' => $insight,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            $this->info('Rapor Kaydedildi!');

        } catch (\Exception $e) {
            $this->error('AI Hatası: ' . $e->getMessage());
        }
    }
}
