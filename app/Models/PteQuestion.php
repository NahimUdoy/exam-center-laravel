<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PteQuestion extends Model
{
    protected $fillable = [
        'external_id', 'type', 'section', 'title', 'instructions',
        'payload', 'image_url', 'correct_text', 'answer_key', 'sort_order',
    ];

    protected $casts = [
        'payload' => 'array',
        'answer_key' => 'array',
    ];
}
