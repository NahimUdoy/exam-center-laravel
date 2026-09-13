<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnglishDrill extends Model
{
    protected $fillable = [
        'english_lesson_id', 'kind', 'prompt', 'sentence', 'options', 'correct', 'explanation', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'correct' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(EnglishLesson::class, 'english_lesson_id');
    }
}
