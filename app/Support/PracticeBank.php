<?php

namespace App\Support;

use App\Models\PracticeQuestion;
use App\Models\TaskType;
use Illuminate\Support\Collection;

class PracticeBank
{
    public const PASSAGE = 'Libraries have always been more than buildings that store books. They are quiet laboratories where communities learn, debate, and reinvent themselves. Today, many libraries invite residents to borrow tools, attend coding workshops, and meet visiting experts who share practical skills. By blending print collections with digital services, these spaces keep knowledge accessible for everyone.';

    public const IDS = [
        '1001648', '1001649', '1001650', '1001651', '1001652', '1001653',
        '1001654', '1001655', '1001656', '1001657', '1001658', '1001659',
    ];

    public static function questions(): Collection
    {
        $items = PracticeQuestion::query()
            ->whereIn('external_id', self::IDS)
            ->get()
            ->sortBy(fn (PracticeQuestion $q) => array_search($q->external_id, self::IDS, true))
            ->values();

        if ($items->isNotEmpty()) {
            return $items;
        }

        return PracticeQuestion::query()->orderBy('id')->limit(12)->get();
    }

    public static function forTask(TaskType $taskType): Collection
    {
        $items = PracticeQuestion::query()
            ->where('task_slug', $taskType->slug)
            ->get();

        if ($items->isEmpty()) {
            return self::questions();
        }

        $originals = $items
            ->filter(fn (PracticeQuestion $q) => in_array($q->external_id, self::IDS, true))
            ->sortBy(fn (PracticeQuestion $q) => array_search($q->external_id, self::IDS, true));

        $rest = $items
            ->reject(fn (PracticeQuestion $q) => in_array($q->external_id, self::IDS, true))
            ->sortBy('external_id');

        return $originals->concat($rest)->values();
    }
}
