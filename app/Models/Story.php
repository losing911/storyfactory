<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        $patterns = \Illuminate\Support\Facades\Cache::remember('lore_patterns', 3600, function () {
            // FIX: LoreEntry uses 'title' not 'baslik'
            $entries = \App\Models\LoreEntry::where('is_active', true)->get(['title', 'slug']);
            $p = [];
            foreach ($entries as $entry) {
                // Pre-compile regex for performance
                $p[] = [
                    'pattern' => '/(?<!<a href="[^"]*">)\b(' . preg_quote($entry->title, '/') . ')(?!\w)\b(?!<\/a>)/iu',
                    'slug' => $entry->slug,
                    'title' => $entry->title
                ];
            }
            return $p;
        });
        
        foreach($patterns as $item) {
            $replacement = '<a href="/database/'.$item['slug'].'" class="text-neon-pink hover:underline border-b border-neon-pink/30" title="Veri Bankası: '.$item['title'].'">$1</a>';
            try {
                // Limit 1 replacement per term per story
                $content = preg_replace($item['pattern'], $replacement, $content, 1);
            } catch (\Exception $e) { continue; }
        }

        return $content;
    }
}
