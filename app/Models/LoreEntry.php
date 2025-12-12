<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoreEntry extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    
    protected $fillable = ['title', 'slug', 'type', 'description', 'visual_prompt', 'image_url', 'is_active'];
}
