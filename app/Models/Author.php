<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = ['name', 'slug', 'bio', 'role', 'avatar', 'is_ai'];

    public function stories()
    {
        return $this->hasMany(Story::class);
    }
}
