<?php

namespace App\Services;

class LoreValidator
{
    /**
     * Yasaklı kelimeler - Neo-Pera evreninde kullanılmaması gereken klişeler
     */
    private const FORBIDDEN_PHRASES = [
        'neon ışıklar',
        'metal yığını',
        'siberuzayda süzülmek',
        'robot kollar',
        'yapay zeka',  // Yerine: 'sentetik zihin', 'veri ruhu' vb. kullan
        'matrix',      // Yerine: 'veri ağı', 'nöral kafes' vb.
        'hacker',      // Yerine: 'netrunner', 'veri kazıyıcı' vb.
    ];

    /**
     * Duyusal kelimeleri tespit etmek için pattern'ler
     */
    private const SENSORY_PATTERNS = [
        'smell' => ['koku', 'kokusu', 'kokulu', 'kokan', 'kokmuş', 'yanık', 'asit', 'sentetik', 'parfüm'],
        'sound' => ['ses', 'sesi', 'sesli', 'uğultu', 'çınlama', 'tık', 'gürültü', 'fısıltı', 'bağırış', 'şırıltı', 'siren'],
        'taste' => ['tat', 'tadı', 'metalik', 'acı', 'tuzlu', 'ekşi'],
        'touch' => ['dokunma', 'yumuşak', 'sert', 'kaygan', 'pürüzlü', 'sıcak', 'soğuk', 'kaşınma', 'titreyiş'],
    ];

    /**
     * İnsan kusurları - karakterlerde olması gereken gerçekçilik unsurları
     */
    private const HUMAN_FLAWS = [
        'kekele', 'unutkan', 'yorgun', 'titreyen', 'terleyen', 'hasta', 'acı', 'ağrı',
        'yanlış', 'hatalı', 'kırık', 'bozuk', 'arızalı', 'kusurlu'
    ];

    /**
     * Yasaklı kelimeleri kontrol et
     *
     * @param string $content
     * @return array Liste: ['found' => bool, 'phrases' => array]
     */
    public function validateForbiddenWords(string $content): array
    {
        $foundPhrases = [];
        $lowerContent = mb_strtolower($content, 'UTF-8');

        foreach (self::FORBIDDEN_PHRASES as $phrase) {
            if (mb_strpos($lowerContent, mb_strtolower($phrase, 'UTF-8')) !== false) {
                $foundPhrases[] = $phrase;
            }
        }

        return [
            'valid' => empty($foundPhrases),
            'found_phrases' => $foundPhrases,
            'message' => empty($foundPhrases) 
                ? 'Yasaklı klis terms yok ✓' 
                : 'Yasaklı kelimeler bulundu: ' . implode(', ', $foundPhrases)
        ];
    }

    /**
     * Duyusal derinlik kontrolü - en az 2 farklı duyu olmalı
     *
     * @param string $content
     * @return array
     */
    public function validateSensoryDepth(string $content): array
    {
        $lowerContent = mb_strtolower($content, 'UTF-8');
        $foundSenses = [];

        foreach (self::SENSORY_PATTERNS as $sense => $patterns) {
            foreach ($patterns as $pattern) {
                if (mb_strpos($lowerContent, $pattern) !== false) {
                    $foundSenses[$sense] = true;
                    break; // Bu duyu için yeterli, diğer pattern'lara bakma
                }
            }
        }

        $senseCount = count($foundSenses);
        $valid = $senseCount >= 2;

        return [
            'valid' => $valid,
            'count' => $senseCount,
            'senses' => array_keys($foundSenses),
            'message' => $valid 
                ? "Duyusal derinlik yeterli ($senseCount duyu) ✓" 
                : "Duyusal derinlik yetersiz ($senseCount/2 duyu)"
        ];
    }

    /**
     * İnsan kusurları kontrolü - karakterlerde gerçekçilik
     *
     * @param string $content
     * @return array
     */
    public function validateHumanFlaws(string $content): array
    {
        $lowerContent = mb_strtolower($content, 'UTF-8');
        $foundFlaws = [];

        foreach (self::HUMAN_FLAWS as $flaw) {
            if (mb_strpos($lowerContent, $flaw) !== false) {
                $foundFlaws[] = $flaw;
            }
        }

        $hasFlaws = !empty($foundFlaws);

        return [
            'valid' => $hasFlaws,
            'flaws' => $foundFlaws,
            'message' => $hasFlaws 
                ? 'Karakter kusurları mevcut ✓' 
                : 'Karakterler çok mükemmel - kusurlar ekle'
        ];
    }

    /**
     * Neo-Pera hikaye kalite skoru (1-10)
     *
     * @param string $content
     * @return array
     */
    public function scoreContent(string $content): array
    {
        $forbiddenCheck = $this->validateForbiddenWords($content);
        $sensoryCheck = $this->validateSensoryDepth($content);
        $flawCheck = $this->validateHumanFlaws($content);

        // Scoring
        $score = 10;
        
        // Yasaklı kelime penalty: -2 her kelime için
        $score -= count($forbiddenCheck['found_phrases']) * 2;
        
        // Duyusal derinlik: Max 3 puan
        if ($sensoryCheck['count'] == 0) $score -= 3;
        elseif ($sensoryCheck['count'] == 1) $score -= 2;
        
        // İnsan kusurları: -2 puan eksikse
        if (!$flawCheck['valid']) $score -= 2;

        $score = max(1, min(10, $score)); // Clamp 1-10

        return [
            'score' => $score,
            'grade' => $this->getGrade($score),
            'forbidden_check' => $forbiddenCheck,
            'sensory_check' => $sensoryCheck,
            'flaw_check' => $flawCheck,
            'recommendations' => $this->getRecommendations($score, $forbiddenCheck, $sensoryCheck, $flawCheck)
        ];
    }

    /**
     * Skor'a göre grade döndür
     */
    private function getGrade(int $score): string
    {
        if ($score >= 9) return 'A+ (Mükemmel Neo-Pera Lore)';
        if ($score >= 7) return 'A (İyi)';
        if ($score >= 5) return 'B (Orta)';
        if ($score >= 3) return 'C (Zayıf)';
        return 'D (Başarısız - Yeniden Yaz)';
    }

    /**
     * İyileştirme önerileri
     */
    private function getRecommendations(int $score, array $forbidden, array $sensory, array $flaw): array
    {
        $recs = [];

        if (!$forbidden['valid']) {
            $recs[] = "Yasaklı kelimeleri değiştir: " . implode(', ', $forbidden['found_phrases']);
        }

        if ($sensory['count'] < 2) {
            $missing = array_diff(['smell', 'sound', 'taste', 'touch'], $sensory['senses']);
            $recs[] = "Duyusal derinlik ekle: " . implode(', ', $missing);
        }

        if (!$flaw['valid']) {
            $recs[] = "Karakter kusurları ekle (kekele, unutkan, yorgun vb.)";
        }

        if ($score >= 8 && empty($recs)) {
            $recs[] = "Hikaye Neo-Pera standartlarına uygun! 🎉";
        }

        return $recs;
    }
}
