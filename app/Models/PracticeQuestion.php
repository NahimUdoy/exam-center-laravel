<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeQuestion extends Model
{
    protected $fillable = [
        'external_id', 'task_type_id', 'skill', 'task_slug', 'title',
        'difficulty', 'appeared', 'is_prediction', 'prompt', 'passage', 'meta',
        'image_url', 'exam_slugs', 'practiced', 'bookmarked',
    ];

    protected $casts = [
        'is_prediction' => 'boolean',
        'practiced' => 'boolean',
        'bookmarked' => 'boolean',
        'meta' => 'array',
        'exam_slugs' => 'array',
    ];

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }
}
