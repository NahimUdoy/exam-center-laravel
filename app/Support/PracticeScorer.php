<?php

namespace App\Support;

use App\Models\PracticeQuestion;

class PracticeScorer
{
    public static function for(PracticeQuestion $item, int $seconds, string $skill, mixed $answer = null): array
    {
        $kind = PracticeKind::for($item->task_slug ?: 'read-aloud');
        $payload = PracticePayload::hydrate($item);
        $key = $payload['answer_key'] ?? null;
        $seconds = max(0, min(180, $seconds));

        $objective = is_array($key) ? self::scoreKey($key, $answer) : null;
        $writing = self::writingScore($kind['mode'], $payload, $answer);
        $speaking = $kind['uses_mic'] ? self::speakingScore($item, $seconds, $skill) : null;

        if ($objective !== null) {
            $overall = $objective['correct'] ? 88 : 42;
            $traits = self::objectiveTraits($overall, $kind['mode']);
            $band = $objective['correct'] ? 'Correct' : 'Incorrect';
            $tips = [
                $objective['correct']
                    ? 'Nice — this item matched the stored answer key.'
                    : 'Compare your answer with the key and retry a similar item today.',
                $objective['reveal'],
            ];
            $note = $objective['correct']
                ? 'Auto-scored from the answer key saved on this question.'
                : 'Auto-scored from the answer key. Review the reveal below.';
        } elseif ($writing !== null) {
            $overall = $writing['overall'];
            $traits = $writing['traits'];
            $band = $writing['band'];
            $tips = $writing['tips'];
            $note = $writing['note'];
        } else {
            $speaking ??= self::speakingScore($item, $seconds, $skill);
            $overall = $speaking['overall'];
            $traits = $speaking['traits'];
            $band = $speaking['band'];
            $tips = $speaking['tips'];
            $note = $speaking['note'];
        }

        if (! empty($payload['sample_answer']) && $kind['uses_text']) {
            $tips[] = 'Sample response: '.$payload['sample_answer'];
        }
        if (! empty($payload['correct_text']) && $kind['uses_mic'] && $item->task_slug === 'answer-short') {
            $tips[] = 'Expected short answer: '.$payload['correct_text'];
        }

        return [
            'overall' => $overall,
            'band' => $band,
            'seconds' => $seconds,
            'correct' => $objective['correct'] ?? null,
            'reveal' => $objective['reveal'] ?? ($payload['correct_text'] ?? $payload['sample_answer'] ?? null),
            'traits' => $traits,
            'tips' => array_values(array_filter($tips)),
            'note' => $note,
        ];
    }

    public static function scoreKey(array $key, mixed $answer): array
    {
        $kind = $key['kind'] ?? null;
        $ok = false;
        $reveal = 'See the stored key.';

        if ($kind === 'single') {
            $ok = (int) $answer === (int) ($key['index'] ?? -1);
            $reveal = 'Correct option: '.chr(65 + (int) ($key['index'] ?? 0));
        } elseif ($kind === 'multi') {
            $a = collect((array) $answer)->map(fn ($v) => (int) $v)->sort()->values()->all();
            $b = collect($key['indexes'] ?? [])->map(fn ($v) => (int) $v)->sort()->values()->all();
            $ok = $a === $b;
            $reveal = 'Correct options: '.collect($b)->map(fn ($i) => chr(65 + $i))->implode(', ');
        } elseif ($kind === 'order') {
            $ok = implode(',', (array) $answer) === implode(',', $key['ids'] ?? []);
            $reveal = 'Correct order: '.implode(' → ', $key['ids'] ?? []);
        } elseif ($kind === 'blanks' || $kind === 'text') {
            if (is_array($answer) && ! array_is_list($answer)) {
                ksort($answer, SORT_NATURAL);
                $answer = array_values($answer);
            }
            $got = self::norm(is_array($answer) ? implode(' ', $answer) : (string) $answer);
            $want = self::norm(implode(' ', $key['values'] ?? [$key['value'] ?? '']));
            $ok = $got !== '' && ($got === $want || str_contains($want, $got) || str_contains($got, $want));
            $reveal = 'Expected: '.($key['value'] ?? implode(', ', $key['values'] ?? []));
        } elseif ($kind === 'hiw') {
            $a = collect((array) $answer)->map(fn ($v) => strtolower(trim((string) $v)))->filter()->sort()->values()->all();
            $b = collect($key['words'] ?? [])->map(fn ($v) => strtolower(trim((string) $v)))->sort()->values()->all();
            $ok = $a === $b;
            $reveal = 'Incorrect words: '.implode(', ', $key['words'] ?? []);
        }

        return ['correct' => $ok, 'reveal' => $reveal];
    }

    private static function writingScore(string $mode, array $payload, mixed $answer): ?array
    {
        if (! in_array($mode, ['write', 'listen-write'], true)) {
            return null;
        }
        if (! empty($payload['answer_key'])) {
            return null;
        }

        $text = is_string($answer) ? trim($answer) : '';
        $words = $text === '' ? 0 : count(preg_split('/\s+/', $text) ?: []);
        $limit = $payload['word_limit'] ?? ['min' => 20, 'max' => 300];
        $min = (int) ($limit['min'] ?? 20);
        $max = (int) ($limit['max'] ?? 300);

        $form = 40;
        if ($words >= $min && $words <= $max) {
            $form = 86;
        } elseif ($words >= (int) ($min * 0.6) && $words <= (int) ($max * 1.25)) {
            $form = 68;
        } elseif ($words > 0) {
            $form = 48;
        }

        $content = min(90, 38 + min($words, $max) * 0.35);
        $grammar = $words > 12 ? 72 : 50;
        $vocab = $words > 20 ? 74 : 52;
        $overall = (int) round(($form + $content + $grammar + $vocab) / 4);
        $band = $overall >= 80 ? 'On-target draft' : ($overall >= 60 ? 'Keep tightening the form' : 'Too short to score well');

        return [
            'overall' => self::clamp($overall),
            'traits' => [
                ['label' => 'Content', 'score' => self::clamp((int) $content)],
                ['label' => 'Form', 'score' => self::clamp($form)],
                ['label' => 'Grammar', 'score' => self::clamp($grammar)],
                ['label' => 'Vocabulary', 'score' => self::clamp($vocab)],
            ],
            'band' => $band,
            'tips' => [
                $words < $min
                    ? "Write at least {$min} words. You wrote {$words}."
                    : ($words > $max ? "Cut back toward {$max} words. You wrote {$words}." : "Word count {$words} is inside {$min}–{$max}."),
                'Use the source ideas. Do not invent numbers that are not in the prompt.',
            ],
            'note' => 'Practice score from length and task form. A teacher/AI review can still lift grammar and vocabulary.',
        ];
    }

    private static function speakingScore(PracticeQuestion $item, int $seconds, string $skill): array
    {
        $hash = abs(crc32($item->external_id.'|'.$seconds.'|'.$skill));
        $base = 64 + ($hash % 14);
        $lengthBonus = match (true) {
            $seconds >= 28 => 12,
            $seconds >= 18 => 8,
            $seconds >= 8 => 3,
            $seconds > 0 => -8,
            default => -22,
        };

        $a = self::clamp($base + $lengthBonus + ($hash % 5));
        $b = self::clamp($base + 4 + (int) ($lengthBonus / 2) + (($hash >> 3) % 7));
        $c = self::clamp($base + $lengthBonus + (($hash >> 5) % 6));
        $d = self::clamp($base + 2 + (($hash >> 7) % 8) + (int) ($lengthBonus / 3));
        $overall = (int) round(($a + $b + $c + $d) / 4);
        $labels = ['Content', 'Pronunciation', 'Fluency', 'Intonation'];

        return [
            'overall' => $overall,
            'traits' => [
                ['label' => $labels[0], 'score' => $a],
                ['label' => $labels[1], 'score' => $b],
                ['label' => $labels[2], 'score' => $c],
                ['label' => $labels[3], 'score' => $d],
            ],
            'band' => match (true) {
                $overall >= 85 => 'Strong attempt',
                $overall >= 75 => 'Solid practice score',
                $overall >= 60 => 'Keep drilling this item',
                default => 'Incomplete delivery',
            },
            'tips' => [
                $seconds < 12
                    ? 'Speak closer to the full record time so content and fluency can score.'
                    : 'Hold a steady pace and finish the last sentence cleanly.',
                'Use a headset on the next try for a clearer pronunciation signal.',
            ],
            'note' => 'Practice score from delivery length. Full AI pronunciation needs a captured headset recording.',
        ];
    }

    private static function objectiveTraits(int $overall, string $mode): array
    {
        $labels = match ($mode) {
            'write', 'listen-write' => ['Content', 'Form', 'Grammar', 'Vocabulary'],
            'mcq', 'listen-mcq', 'fib-select', 'fib-bank', 'reorder' => ['Accuracy', 'Vocabulary', 'Coherence', 'Detail'],
            'hiw' => ['Accuracy', 'Listening', 'Detail', 'Spelling'],
            default => ['Content', 'Pronunciation', 'Fluency', 'Intonation'],
        };
        $delta = [0, -4, 3, -2];

        return collect($labels)->map(fn ($label, $i) => [
            'label' => $label,
            'score' => self::clamp($overall + $delta[$i]),
        ])->all();
    }

    private static function norm(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    }

    private static function clamp(int $value): int
    {
        return max(28, min(96, $value));
    }
}
