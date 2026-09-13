<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyPlan extends Model
{
    protected $fillable = [
        'user_id', 'exam_slug', 'tier', 'level_label', 'plan_json',
    ];

    protected $casts = [
        'plan_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
