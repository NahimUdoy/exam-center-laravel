<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'slug', 'name', 'short_name', 'tagline', 'accent', 'accent_soft',
        'description', 'question_count', 'free_practice', 'cover_image', 'icon',
    ];

    protected $casts = [
        'free_practice' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
