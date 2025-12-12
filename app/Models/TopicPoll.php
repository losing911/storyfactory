<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicPoll extends Model
{
    protected $fillable = ['target_date', 'options', 'winning_topic', 'is_processed'];

    protected $casts = [
        'options' => 'array',
        'target_date' => 'date'
    ];
}
