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
}
