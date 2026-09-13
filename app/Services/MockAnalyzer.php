<?php

namespace App\Services;

use App\Models\MockQuestion;
use App\Models\MockTest;
use App\Models\PteQuestion;
use Illuminate\Support\Collection;

class MockAnalyzer
{
    public static function analyzeIeltsReading(MockTest $mock, array $answers): array
    {
        $items = [];
        foreach ($mock->examQuestions as $q) {
            $selected = $answers[$q->number] ?? $answers[(string) $q->number] ?? null;
            $skipped = $selected === null || $selected === '';
            $correct = ! $skipped && (int) $selected === (int) $q->correct_index;
            $items[] = [
                'id' => (string) $q->id,
                'number' => $q->number,
                'title' => $q->prompt,
                'skill' => $q->skill_tag ?: 'Reading',
                'section' => 'Part '.$q->part,
                'verdict' => $skipped ? 'skipped' : ($correct ? 'correct' : 'incorrect'),
                'your_answer' => $skipped ? 'Not answered' : ($q->options[(int) $selected] ?? '—'),
                'correct_answer' => $q->options[$q->correct_index] ?? '—',
                'tip' => $q->tip ?: 'Re-read the paragraph and eliminate wrong options.',
                'leakage' => $skipped
                    ? 'Time / attempt leakage — marks left on the table.'
                    : ($correct ? null : (($q->skill_tag ?: 'Reading').' leakage')),
            ];
        }

        return self::summarize($items, $mock->title, strtoupper($mock->exam_slug).' Reading');
    }

    public static function analyzePte(MockTest $mock, Collection $bank, array $answers): array
    {
        $items = [];
        foreach ($bank->values() as $i => $q) {
            /** @var PteQuestion $q */
            $answer = $answers[$q->external_id] ?? null;
            $empty = $answer === null || $answer === '' || $answer === [];
            $key = $q->answer_key;
            $skill = strtoupper($q->type);

            if (! $key) {
                $items[] = [
                    'id' => $q->external_id,
                    'number' => $i + 1,
                    'title' => $q->title,
                    'skill' => $skill,
                    'section' => $q->section,
                    'verdict' => $empty ? 'skipped' : 'attempted',
                    'your_answer' => $empty ? 'Not answered' : self::stringify($answer),
                    'correct_answer' => $q->correct_text ?: 'Coach / AI review',
                    'tip' => 'Review the task template and redo 5 similar items.',
                    'leakage' => $empty ? 'Attempt leakage' : 'Needs human/AI review',
                ];
                continue;
            }

            if ($empty) {
                $items[] = [
                    'id' => $q->external_id,
                    'number' => $i + 1,
                    'title' => $q->title,
                    'skill' => $skill,
                    'section' => $q->section,
                    'verdict' => 'skipped',
                    'your_answer' => 'Not answered',
                    'correct_answer' => self::explainKey($key),
                    'tip' => 'Do not leave auto-scored items blank.',
                    'leakage' => 'Skipped item — free marks lost.',
                ];
                continue;
            }

            $ok = self::scoreKey($key, $answer);
            $items[] = [
                'id' => $q->external_id,
                'number' => $i + 1,
                'title' => $q->title,
                'skill' => $skill,
                'section' => $q->section,
                'verdict' => $ok ? 'correct' : 'incorrect',
                'your_answer' => self::stringify($answer),
                'correct_answer' => self::explainKey($key),
                'tip' => 'Compare your answer to the key and redo this task type today.',
                'leakage' => $ok ? null : $skill.' leakage — pattern mismatch.',
            ];
        }

        return self::summarize($items, $mock->title, 'PTE Academic');
    }

    private static function scoreKey(array $key, mixed $answer): bool
    {
        $kind = $key['kind'] ?? null;
        if ($kind === 'single') {
            return (int) $answer === (int) ($key['index'] ?? -1);
        }
        if ($kind === 'multi') {
            $a = collect((array) $answer)->map(fn ($v) => (int) $v)->sort()->values()->all();
            $b = collect($key['indexes'] ?? [])->map(fn ($v) => (int) $v)->sort()->values()->all();

            return $a === $b;
        }
        if ($kind === 'order') {
            return implode(',', (array) $answer) === implode(',', $key['ids'] ?? []);
        }
        if ($kind === 'blanks' || $kind === 'text') {
            if (is_array($answer) && ! array_is_list($answer)) {
                ksort($answer, SORT_NATURAL);
                $answer = array_values($answer);
            }
            $got = strtolower(trim(is_array($answer) ? implode(' ', $answer) : (string) $answer));
            $want = strtolower(implode(' ', $key['values'] ?? [$key['value'] ?? '']));

            return $got === $want || str_contains($got, $want) || str_contains($want, $got);
        }
        if ($kind === 'hiw') {
            $arr = collect((array) $answer)->map(fn ($w) => strtolower((string) $w))->all();
            foreach ($key['words'] ?? [] as $w) {
                if (! collect($arr)->contains(fn ($a) => str_contains($a, strtolower($w)))) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    private static function explainKey(array $key): string
    {
        return match ($key['kind'] ?? '') {
            'single' => 'Option #'.(($key['index'] ?? 0) + 1),
            'multi' => 'Options: '.implode(', ', array_map(fn ($i) => $i + 1, $key['indexes'] ?? [])),
            'order' => implode(' → ', $key['ids'] ?? []),
            'blanks' => implode(' / ', $key['values'] ?? []),
            'text' => $key['value'] ?? '—',
            'hiw' => implode(', ', $key['words'] ?? []),
            default => 'See key',
        };
    }

    private static function stringify(mixed $answer): string
    {
        if (is_array($answer)) {
            return implode(', ', array_map('strval', $answer));
        }

        $s = (string) $answer;

        return strlen($s) > 120 ? substr($s, 0, 120).'…' : $s;
    }

    private static function summarize(array $items, string $title, string $examLabel): array
    {
        $correct = collect($items)->where('verdict', 'correct')->count();
        $incorrect = collect($items)->where('verdict', 'incorrect')->count();
        $skipped = collect($items)->where('verdict', 'skipped')->count();
        $attemptedUnscored = collect($items)->where('verdict', 'attempted')->count();
        $scored = $correct + $incorrect;
        $scorePct = $scored === 0 ? ($attemptedUnscored > 0 ? 70 : 0) : (int) round(($correct / $scored) * 100);

        $leaks = collect($items)->groupBy('skill')->map(function ($list, $skill) {
            $c = $list->where('verdict', 'correct')->count();
            $w = $list->where('verdict', 'incorrect')->count();
            $s = $list->where('verdict', 'skipped')->count();
            $denom = $c + $w;
            $accuracy = $denom === 0 ? 0 : (int) round(($c / $denom) * 100);
            $level = $accuracy >= 80 ? 'strong' : ($accuracy >= 60 ? 'ok' : ($accuracy >= 40 ? 'leak' : 'critical'));

            return [
                'skill' => $skill,
                'section' => $list->first()['section'] ?? '',
                'attempted' => $c + $w,
                'correct' => $c,
                'incorrect' => $w,
                'skipped' => $s,
                'accuracy' => $accuracy,
                'level' => $level,
                'advice' => match ($level) {
                    'strong' => "$skill is a strength — keep light maintenance.",
                    'ok' => "$skill is okay — tighten with timed micro-sets.",
                    'leak' => "$skill is leaking marks — practice this first tomorrow.",
                    default => "$skill needs urgent repair — 20-min focused block next.",
                },
            ];
        })->sortBy('accuracy')->values()->all();

        $weak = collect($leaks)->whereIn('level', ['leak', 'critical']);

        return [
            'mock_title' => $title,
            'exam_label' => $examLabel,
            'score_pct' => $scorePct,
            'band_label' => $scorePct >= 75 ? 'Strong · polish weak spots' : ($scorePct >= 45 ? 'Developing · seal the leaks' : 'Foundation · rebuild with short wins'),
            'correct' => $correct,
            'incorrect' => $incorrect,
            'skipped' => $skipped,
            'attempted_unscored' => $attemptedUnscored,
            'strengths' => collect($leaks)->where('level', 'strong')->pluck('skill')->all() ?: ['Showing up'],
            'leaks' => $leaks,
            'total_scored' => $scored,
            'total_items' => count($items),
            'top_fixes' => $weak->take(3)->pluck('advice')->merge(
                collect($items)->where('verdict', 'incorrect')->take(2)->map(fn ($i) => 'Q'.$i['number'].': '.$i['tip'])
            )->take(5)->values()->all() ?: ['Great attempt — retake a sectional this week.'],
            'items' => $items,
            'summary' => $incorrect + $skipped === 0
                ? 'Clean attempt. Protect this energy with a short review.'
                : 'You lost marks mainly in '.($weak->first()['skill'] ?? 'mixed skills').'. Fix the leaks below before repeating a full mock.',
        ];
    }
}
