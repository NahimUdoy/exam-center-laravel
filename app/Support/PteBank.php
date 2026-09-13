<?php

namespace App\Support;

use App\Models\PteQuestion;
use Illuminate\Support\Collection;

class PteBank
{
    public const EXTRA_SORT_START = 1000;

    public static function forMock(string $mockSlug): Collection
    {
        $all = PteQuestion::query()->orderBy('sort_order')->get();
        $core = $all->filter(fn (PteQuestion $q) => $q->sort_order < self::EXTRA_SORT_START)->values();
        $extra = $all->filter(fn (PteQuestion $q) => $q->sort_order >= self::EXTRA_SORT_START)->values();

        $speaking = ['read-aloud', 'repeat-sentence', 'describe-image', 'retell-lecture', 'answer-short'];
        $writing = ['swt', 'essay'];

        return match ($mockSlug) {
            'pte-full-a' => $core,
            'pte-full-b', 'pte-full-c' => $extra->isNotEmpty() ? $extra : $core,
            'pte-s-ra-rs' => $core->filter(fn (PteQuestion $q) => in_array($q->type, $speaking, true))->values(),
            'pte-s-plus' => self::prefer($extra, $core, fn (PteQuestion $q) => in_array($q->type, $speaking, true)),
            'pte-w-swt-we' => $core->filter(fn (PteQuestion $q) => in_array($q->type, $writing, true))->values(),
            'pte-w-plus' => self::prefer($extra, $core, fn (PteQuestion $q) => in_array($q->type, $writing, true)),
            'pte-r-rwfib' => $core->filter(fn (PteQuestion $q) => $q->section === 'reading')->values(),
            'pte-r-plus' => self::prefer($extra, $core, fn (PteQuestion $q) => $q->section === 'reading'),
            'pte-l-wfd' => $core->filter(fn (PteQuestion $q) => $q->section === 'listening')->values(),
            'pte-l-plus' => self::prefer($extra, $core, fn (PteQuestion $q) => $q->section === 'listening'),
            default => $core,
        };
    }

    private static function prefer(Collection $extra, Collection $core, callable $filter): Collection
    {
        $fromExtra = $extra->filter($filter)->values();

        return $fromExtra->isNotEmpty() ? $fromExtra : $core->filter($filter)->values();
    }
}
