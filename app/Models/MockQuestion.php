<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockQuestion extends Model
{
    protected $fillable = [
        'mock_test_id', 'number', 'part', 'prompt', 'options',
        'correct_index', 'skill_tag', 'tip',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function mockTest(): BelongsTo
    {
        return $this->belongsTo(MockTest::class);
    }
}
