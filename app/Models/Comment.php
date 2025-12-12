<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['story_id', 'nickname', 'message', 'ip_address', 'is_approved'];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
