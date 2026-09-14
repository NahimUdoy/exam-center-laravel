<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeakingSession extends Model
{
    protected $fillable = [
        'user_id', 'exam_slug', 'status', 'turns', 'seconds', 'score',
    ];

    protected $casts = [
        'turns' => 'array',
        'score' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
