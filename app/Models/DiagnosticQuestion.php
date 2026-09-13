<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticQuestion extends Model
{
    protected $fillable = [
        'exam_slug', 'external_id', 'prompt', 'options', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
    ];
}
