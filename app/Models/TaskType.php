<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    protected $fillable = [
        'slug', 'skill', 'name', 'done', 'total', 'exam_slugs',
    ];

    protected $casts = [
        'exam_slugs' => 'array',
    ];

    public function scopeForExam($query, string $examSlug)
    {
        return $query->whereJsonContains('exam_slugs', $examSlug);
    }
}
