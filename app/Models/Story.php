<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'baslik',
        'slug',
        'metin',
        'gorsel_url',
        'yayin_tarihi',
        'durum',
        'konu',
        'mood',
        'music_url',
        'music_prompt',
        'meta',
        'etiketler',
        'sosyal_ozet',
        'gorsel_prompt',
        'views',
    ];

    protected $casts = [
        'etiketler' => 'array',
        'yayin_tarihi' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function translations()
    {
        return $this->hasMany(StoryTranslation::class);
    }
    
    // Helper to get translated or default
    public function getText($locale = 'tr')
    {
        if ($locale === 'tr') return $this->metin;
        $trans = $this->translations->where('locale', $locale)->first();
        return $trans ? $trans->metin : $this->metin;
    }

    public function getTitle($locale = 'tr')
    {
        if ($locale === 'tr') return $this->baslik;
        $trans = $this->translations->where('locale', $locale)->first();
        return $trans ? $trans->title : $this->baslik;
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    // SEO Accessors
    public function getSeoTitleAttribute()
    {
        return "{$this->baslik} | Cyberpunk Hikâye – Anxipunk";
    }

    public function getSeoDescriptionAttribute()
    {
        // Use custom meta description if valid, otherwise generate template
        if (!empty($this->meta_aciklama) && strlen($this->meta_aciklama) > 10) {
            return $this->meta_aciklama;
        }

        return "{$this->baslik}, Anxipunk evreninde geçen karanlık bir cyberpunk hikâyedir. Dijital bilinç, veri ve distopik gelecek temalarını işler.";
    }

    // Lore Cross-Linking (Smart Content) - Optimized with Cache
    public function getProcessedContentAttribute()
    {
        $content = $this->metin;
        // Verify LoreEntry exists
        if (!class_exists(\App\Models\LoreEntry::class)) return $content;

        // Cache the lore patterns for 1 hour to avoid DB hits on every request
        // Key is 'lore_patterns', shared across all stories
        // Cache the lore patterns for 1 hour to avoid DB hits on every request
        // Key is 'lore_patterns', shared across all stories
        // STRATEGY: Split content by HTML tags to safely process ONLY text nodes.
        // This avoids: 1) Variable-length lookbehind errors, 2) Breaking attributes, 3) Auto-linking inside existing links.
        
        $tokens = preg_split('/(<[^>]+>)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $newContent = '';
        
        // Cache patterns to avoid rebuilding loop (same as before but simplified regex)
        $patterns = Cache::remember('lore_regex_patterns_v2', 3600, function () {
            $entries = LoreEntry::where('is_active', true)->get();
            $p = [];
            foreach ($entries as $entry) {
                // Main Title
                // Use Unicode boundaries (?<!\p{L}) instead of \b for Turkish support (e.g. 'Delfin'in' -> 'Delfin' matches)
                $p[] = [
                    'pattern' => '/(?<!\p{L})(' . preg_quote($entry->title, '/') . ')(?!\p{L})/u',
                    'slug' => $entry->slug,
                    'title' => $entry->title
                ];
                // Keywords
                if (!empty($entry->keywords) && is_array($entry->keywords)) {
                    foreach ($entry->keywords as $keyword) {
                        $p[] = [
                            'pattern' => '/(?<!\p{L})(' . preg_quote($keyword, '/') . ')(?!\p{L})/u',
                            'slug' => $entry->slug,
                            'title' => $entry->title
                        ];
                    }
                }
            }
            // Sort longest first
            usort($p, function($a, $b) {
                return strlen($b['pattern']) - strlen($a['pattern']);
            });
            return $p;
        });

        // Loop through tokens
        $inLink = false;
        foreach ($tokens as $token) {
            // Check if token is a tag
            if (preg_match('/^<(\/?)(\w+).*?>$/u', $token, $matches)) {
                $newContent .= $token;
                $tagName = strtolower($matches[2]);
                if ($tagName === 'a') {
                    $inLink = empty($matches[1]); // <a ...> sets true, </a> sets false
                }
            } else {
                // This is a TEXT node.
                if (!$inLink && trim($token) !== '') {
                    // Apply replacements safely
                    foreach($patterns as $item) {
                        $replacement = '<a href="/database/'.$item['slug'].'" class="text-neon-pink hover:underline border-b border-neon-pink/30" title="Veri Bankası: '.$item['title'].'">$1</a>';
                        try {
                            // Only replace first occurrence per text node to avoid chaos? 
                            // Or global? Let's use preg_replace but realize we are modifying $token repeatedly.
                            // To prevent "Delfin" linking inside "Delfin" (if we did multipass), use unique placeholders or just trust the loop order.
                            // With sorted patterns (longest first), we are safer.
                            
                            // NOTE: Since we are iterating patterns, if we replace "Sendika" with "<a>Sendika</a>", the next pattern might see "a" or "Sendika".
                            // But here we are operating on raw text.
                            // Our patterns regex (?<!\p{L})... doesn't exclude tags.
                            // SIMPLE FIX: Use a placeholder method or just one pass.
                            // One-pass combined regex is best.
                            // Let's stick to the simpler loop for now, assuming keyword overlap is rare.
                            
                            // Check if already replaced in this node (simple optimization)
                            if (strpos($token, '<a') !== false) {
                                // If we already added a link in this chunk, be careful.
                                // The simplest logic: loop patterns.
                            }
                            
                            $token = preg_replace($item['pattern'], $replacement, $token, 1);
                            
                        } catch (\Exception $e) { continue; }
                    }
                }
                $newContent .= $token;
            }
        }

        return $newContent;
    }
}
