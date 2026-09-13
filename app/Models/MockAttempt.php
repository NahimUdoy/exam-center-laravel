<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockAttempt extends Model
{
    protected $fillable = [
        'user_id', 'mock_test_id', 'score_pct', 'analysis_json', 'answers_json',
    ];

    protected $casts = [
        'analysis_json' => 'array',
        'answers_json' => 'array',
    ];

    public function mockTest(): BelongsTo
    {
        return $this->belongsTo(MockTest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
