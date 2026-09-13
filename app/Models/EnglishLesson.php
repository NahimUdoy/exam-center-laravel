<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnglishLesson extends Model
{
    protected $fillable = [
        'slug', 'letter', 'category', 'title', 'subtitle', 'level',
        'sort_order', 'summary', 'body', 'rules', 'examples',
    ];

    protected $casts = [
        'rules' => 'array',
        'examples' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function drills(): HasMany
    {
        return $this->hasMany(EnglishDrill::class)->orderBy('sort_order');
    }

    public function related(int $limit = 4)
    {
        return static::query()
            ->where('category', $this->category)
            ->where('id', '!=', $this->id)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public static function categoryMeta(): array
    {
        return [
            'parts-of-speech' => ['label' => 'Parts of speech', 'color' => '#059669', 'soft' => '#d1fae5'],
            'tenses' => ['label' => 'Tenses', 'color' => '#2563eb', 'soft' => '#dbeafe'],
            'grammar' => ['label' => 'Core grammar', 'color' => '#7c3aed', 'soft' => '#ede9fe'],
            'mechanics' => ['label' => 'Writing mechanics', 'color' => '#ea580c', 'soft' => '#ffedd5'],
            'vocabulary' => ['label' => 'Vocabulary', 'color' => '#db2777', 'soft' => '#fce7f3'],
            'everyday' => ['label' => 'Everyday English', 'color' => '#0ea5e9', 'soft' => '#e0f2fe'],
        ];
    }
}
