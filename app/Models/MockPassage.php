<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockPassage extends Model
{
    protected $fillable = ['mock_test_id', 'label', 'text', 'sort_order'];

    public function mockTest(): BelongsTo
    {
        return $this->belongsTo(MockTest::class);
    }
}
