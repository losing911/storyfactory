<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoreEntry extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    
    protected $fillable = ['title', 'slug', 'type', 'description', 'visual_prompt', 'visual_variations', 'image_url', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'visual_variations' => 'array',
    ];
}
