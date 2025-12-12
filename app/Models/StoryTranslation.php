<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['story_id', 'locale', 'title', 'metin', 'social_ozet'];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
