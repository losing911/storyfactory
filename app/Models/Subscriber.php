<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $fillable = [
        'email',
        'name',
        'unsubscribe_token',
        'ip_address',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope to get only active subscribers
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
