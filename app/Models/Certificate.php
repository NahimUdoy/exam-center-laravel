<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'slug', 'exam_slug', 'category', 'title', 'subtitle', 'description',
        'status', 'score_label', 'score_value', 'issued_on', 'meta',
        'cover_image', 'badge_image',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function recipient(): string
    {
        return $this->meta['recipient']
            ?? auth()->user()->name
            ?? 'NextVerseSkill Learner';
    }

    public function credentialId(): string
    {
        return $this->meta['credential_id'] ?? 'NV-CREDENTIAL';
    }

    public function hoursCompleted(): int|float
    {
        return $this->meta['hours_completed'] ?? 0;
    }

    public function modules(): array
    {
        return $this->meta['modules'] ?? [];
    }

    public function level(): string
    {
        return $this->meta['level'] ?? 'Proficient';
    }
}
