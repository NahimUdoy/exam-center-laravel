<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockTest extends Model
{
    protected $fillable = [
        'slug', 'exam_slug', 'section', 'title', 'subtitle', 'duration_min',
        'questions', 'attempts', 'difficulty', 'featured', 'runner', 'cover_image',
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function passages(): HasMany
    {
        return $this->hasMany(MockPassage::class)->orderBy('sort_order');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(MockQuestion::class)->orderBy('number');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(MockAttempt::class);
    }

    public function loadExamBank(): self
    {
        $this->load(['passages', 'examQuestions']);

        if ($this->examQuestions->isNotEmpty()) {
            return $this;
        }

        $canonical = static::query()
            ->where('slug', 'ielts-r-rising-sum')
            ->with(['passages', 'examQuestions'])
            ->first();

        if ($canonical) {
            $this->setRelation('passages', $canonical->passages);
            $this->setRelation('examQuestions', $canonical->examQuestions);
        }

        return $this;
    }
}
