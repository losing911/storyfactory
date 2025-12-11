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
}
