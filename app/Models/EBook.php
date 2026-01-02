<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EBook extends Model
{
    protected $fillable = [
        'title', 'slug', 'volume_number', 'content', 
        'start_story_id', 'end_story_id', 'cover_image_url', 'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean'
    ];
}
