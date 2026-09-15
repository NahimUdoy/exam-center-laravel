<?php

namespace App\Support\Cms;

use App\Models\TaskType;

class QuestionEntry
{
    public const MCQ_SINGLE = ['rc-mcq', 'l-mcq', 'mc-single', 'l-mc-single', 'highlight-summary', 'missing-word'];

    public const MCQ_MULTI = ['mc-multi', 'l-mc-multi'];

    public const RW_FIB = ['rw-fib'];

    public const R_FIB = ['r-fib'];

    public const L_FIB = ['l-fib'];

    public const REORDER = ['reorder'];

    public const HIW = ['hiw', 'highlight-incorrect'];

    public const SPEAKING = ['read-aloud', 'repeat-sentence', 'describe-image', 'retell-lecture', 'answer-short', 'cue-card', 'interview'];

    public const WRITING = ['swt', 'essay', 'task1', 'case-write', 'sst'];

    public const AUDIO = [
        'repeat-sentence', 'retell-lecture', 'answer-short', 'sst', 'wfd', 'hiw',
        'highlight-incorrect', 'l-mcq', 'l-mc-single', 'l-mc-multi', 'l-fib',
        'highlight-summary', 'missing-word',
    ];

    public const IMAGE = ['describe-image', 'task1'];

    public const CORRECT_TEXT = ['answer-short', 'wfd'];

    public static function visible(array $field, array $form): bool
    {
        $when = $field['showWhen'] ?? null;
        if (! $when) {
            return true;
        }
        $current = (string) ($form[$when['key'] ?? ''] ?? '');
        $equals = (array) ($when['equals'] ?? []);

        if (in_array($current, $equals, true)) {
            return true;
        }

        $skill = self::skillFor($current);

        return match (true) {
            $skill === 'reading' && $equals === self::MCQ_SINGLE => true,
            $skill === 'writing' && $equals === self::WRITING => true,
            $skill === 'speaking' && $equals === self::SPEAKING => true,
            $skill === 'listening' && in_array($equals, [self::AUDIO, self::MCQ_SINGLE], true) => true,
            default => false,
        };
    }

    private static function skillFor(string $taskSlug): ?string
    {
        if ($taskSlug === '') {
            return null;
        }

        return TaskType::query()->where('slug', $taskSlug)->value('skill');
    }

    public static function expand(array $form, string $slug): array
    {
        $form['mcqOptions'] = self::expandMcq($form);
        $form['blankRows'] = self::expandBlankRows($form);
        $form['blankAnswers'] = self::asStringList($form['blankAnswers'] ?? ($form['answerKey']['values'] ?? []));
        $form['extraWords'] = self::asStringList($form['extraWords'] ?? []);
        $form['paragraphRows'] = self::expandParagraphs($form);
        $form['hiwWords'] = self::expandHiw($form);
        $form['hiwDraft'] = $form['hiwDraft'] ?? '';

        $passageKey = array_key_exists('text', $form) ? 'text' : 'passage';
        $tokens = self::asList($form['passageTokens'] ?? []);
        if ($tokens !== [] && self::isFib($slug)) {
            $form[$passageKey] = self::passageFromTokens($tokens);
        }

        if (self::isRFib($slug) && ($form['blankAnswers'] === [] || $form['blankAnswers'] === [''])) {
            $values = self::asStringList($form['answerKey']['values'] ?? []);
            $form['blankAnswers'] = $values;
            $bank = self::asStringList($form['wordBank'] ?? []);
            $form['extraWords'] = array_values(array_filter($bank, fn ($w) => ! in_array($w, $values, true)));
        }

        $needed = self::blankCount((string) ($form[$passageKey] ?? ''));
        if (self::isRwFib($slug)) {
            $form['blankRows'] = self::padRows($form['blankRows'], $needed, fn () => ['correct' => '', 'others' => '']);
        }
        if (self::isRFib($slug) || self::isLFib($slug)) {
            $form['blankAnswers'] = self::padRows($form['blankAnswers'], $needed, fn () => '');
        }

        if (self::isMcq($slug) && count($form['mcqOptions']) < 2) {
            $form['mcqOptions'] = array_pad($form['mcqOptions'], 4, ['text' => '', 'correct' => false]);
        }
        if (self::isReorder($slug) && $form['paragraphRows'] === []) {
            $form['paragraphRows'] = [['text' => ''], ['text' => '']];
        }

        return $form;
    }

    public static function compile(array $data, string $slug): array
    {
        $out = [];

        if (self::isMcq($slug)) {
            $choices = [];
            $indexes = [];
            foreach (array_values($data['mcqOptions'] ?? []) as $i => $row) {
                $text = trim((string) ($row['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                if (self::truthy($row['correct'] ?? false)) {
                    $indexes[] = count($choices);
                }
                $choices[] = $text;
            }
            $out['options'] = $choices;
            $out['multi'] = self::isMcqMulti($slug);
            if (self::isMcqMulti($slug)) {
                $out['answer_key'] = ['kind' => 'multi', 'indexes' => $indexes];
            } else {
                $out['answer_key'] = ['kind' => 'single', 'index' => $indexes[0] ?? 0];
            }
        }

        $passageKey = array_key_exists('text', $data) ? 'text' : 'passage';
        $passage = (string) ($data[$passageKey] ?? '');

        if (self::isRwFib($slug)) {
            $built = self::compileRwFib($passage, $data['blankRows'] ?? []);
            $out = array_merge($out, $built);
            $out[$passageKey] = self::passageFromTokens($built['passage_tokens']);
        }

        if (self::isRFib($slug) || self::isLFib($slug)) {
            $answers = self::asStringList($data['blankAnswers'] ?? []);
            $built = self::compileGapPassage($passage, $answers);
            $out['passage_tokens'] = $built['tokens'];
            $out['answer_key'] = ['kind' => 'blanks', 'values' => $built['values']];
            $out[$passageKey] = self::passageFromTokens($built['tokens']);
            if (self::isRFib($slug)) {
                $extra = self::asStringList($data['extraWords'] ?? $data['wordBank'] ?? []);
                $out['word_bank'] = array_values(array_unique(array_filter(array_merge($built['values'], $extra))));
            }
        }

        if (self::isReorder($slug)) {
            $paragraphs = [];
            $ids = [];
            foreach (array_values($data['paragraphRows'] ?? $data['paragraphs'] ?? []) as $i => $row) {
                $text = trim((string) (is_array($row) ? ($row['text'] ?? '') : $row));
                if ($text === '') {
                    continue;
                }
                $id = (string) ($row['id'] ?? chr(97 + $i));
                $ids[] = $id;
                $paragraphs[] = ['id' => $id, 'text' => $text];
            }
            $out['paragraphs'] = $paragraphs;
            $out['answer_key'] = ['kind' => 'order', 'ids' => $ids];
        }

        if (self::isHiw($slug)) {
            $words = [];
            $incorrect = [];
            foreach (array_values($data['hiwWords'] ?? $data['transcript'] ?? []) as $row) {
                if (! is_array($row)) {
                    $row = ['word' => (string) $row, 'incorrect' => false];
                }
                $word = trim((string) ($row['word'] ?? ''));
                if ($word === '') {
                    continue;
                }
                $bad = self::truthy($row['incorrect'] ?? false);
                $words[] = ['word' => $word, 'incorrect' => $bad];
                if ($bad) {
                    $incorrect[] = $word;
                }
            }
            $out['transcript_with_errors'] = $words;
            $out['answer_key'] = ['kind' => 'hiw', 'words' => $incorrect];
        }

        if (self::inGroup($slug, self::CORRECT_TEXT) && trim((string) ($data['correctText'] ?? '')) !== '') {
            $out['correct_text'] = trim((string) $data['correctText']);
            $out['answer_key'] = $out['answer_key'] ?? ['kind' => 'text', 'value' => $out['correct_text']];
        }

        return $out;
    }

    public static function splitWords(string $text): array
    {
        $parts = preg_split('/\s+/', trim($text)) ?: [];

        return array_values(array_filter($parts, fn ($w) => $w !== ''));
    }

    public static function isMcq(string $slug): bool
    {
        return self::inGroup($slug, self::MCQ_SINGLE) || self::inGroup($slug, self::MCQ_MULTI);
    }

    public static function isMcqMulti(string $slug): bool
    {
        return self::inGroup($slug, self::MCQ_MULTI);
    }

    public static function isRwFib(string $slug): bool
    {
        return self::inGroup($slug, self::RW_FIB);
    }

    public static function isRFib(string $slug): bool
    {
        return self::inGroup($slug, self::R_FIB);
    }

    public static function isLFib(string $slug): bool
    {
        return self::inGroup($slug, self::L_FIB);
    }

    public static function isFib(string $slug): bool
    {
        return self::isRwFib($slug) || self::isRFib($slug) || self::isLFib($slug);
    }

    public static function isReorder(string $slug): bool
    {
        return self::inGroup($slug, self::REORDER);
    }

    public static function isHiw(string $slug): bool
    {
        return self::inGroup($slug, self::HIW);
    }

    private static function inGroup(string $slug, array $group): bool
    {
        return in_array($slug, $group, true);
    }

    private static function expandMcq(array $form): array
    {
        if (! empty($form['mcqOptions']) && is_array($form['mcqOptions'])) {
            return array_values(array_map(function ($row) {
                if (is_string($row)) {
                    return ['text' => $row, 'correct' => false];
                }

                return [
                    'text' => (string) ($row['text'] ?? $row['label'] ?? ''),
                    'correct' => self::truthy($row['correct'] ?? false),
                ];
            }, $form['mcqOptions']));
        }

        $options = self::asStringList($form['options'] ?? []);
        $key = is_array($form['answerKey'] ?? null) ? $form['answerKey'] : [];
        $indexes = [];
        if (isset($key['indexes']) && is_array($key['indexes'])) {
            $indexes = array_map('intval', $key['indexes']);
        } elseif (isset($key['index']) && $key['index'] !== '' && $key['index'] !== null) {
            $indexes = [(int) $key['index']];
        } elseif (isset($form['correctIndex']) && $form['correctIndex'] !== '' && $form['correctIndex'] !== null) {
            $indexes = [(int) $form['correctIndex']];
        } elseif (! empty($form['correctIndexes'])) {
            $indexes = array_map('intval', (array) $form['correctIndexes']);
        }

        $rows = [];
        foreach ($options as $i => $text) {
            $rows[] = ['text' => $text, 'correct' => in_array($i, $indexes, true)];
        }

        return $rows;
    }

    private static function expandBlankRows(array $form): array
    {
        if (! empty($form['blankRows']) && is_array($form['blankRows'])) {
            return array_values(array_map(function ($row) {
                $others = $row['others'] ?? [];
                if (is_array($others)) {
                    $others = implode(', ', $others);
                }

                return [
                    'correct' => (string) ($row['correct'] ?? ''),
                    'others' => (string) $others,
                ];
            }, $form['blankRows']));
        }

        $blanks = self::asList($form['blanks'] ?? []);
        $values = self::asStringList($form['answerKey']['values'] ?? []);
        $rows = [];
        foreach ($blanks as $i => $blank) {
            $choices = self::asStringList($blank['choices'] ?? []);
            $correct = $values[$i] ?? ($choices[0] ?? '');
            $others = array_values(array_filter($choices, fn ($c) => strcasecmp($c, $correct) !== 0));
            $rows[] = ['correct' => $correct, 'others' => implode(', ', $others)];
        }

        return $rows;
    }

    private static function expandParagraphs(array $form): array
    {
        $rows = $form['paragraphRows'] ?? $form['paragraphs'] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_map(function ($row) {
            if (is_string($row)) {
                return ['text' => $row];
            }

            return ['id' => $row['id'] ?? null, 'text' => (string) ($row['text'] ?? '')];
        }, $rows));
    }

    private static function expandHiw(array $form): array
    {
        $rows = $form['hiwWords'] ?? $form['transcript'] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_map(function ($row) {
            if (is_string($row)) {
                return ['word' => $row, 'incorrect' => false];
            }

            return [
                'word' => (string) ($row['word'] ?? ''),
                'incorrect' => self::truthy($row['incorrect'] ?? false),
            ];
        }, $rows));
    }

    private static function compileRwFib(string $passage, array $rows): array
    {
        $parts = preg_split('/_{3,}/', $passage) ?: [$passage];
        $blankCount = max(0, count($parts) - 1);
        $tokens = [];
        $blanks = [];
        $values = [];
        foreach ($parts as $i => $part) {
            foreach (preg_split('/\s+/', trim($part)) ?: [] as $word) {
                if ($word !== '') {
                    $tokens[] = $word;
                }
            }
            if ($i >= $blankCount) {
                continue;
            }
            $row = $rows[$i] ?? [];
            $correct = trim((string) ($row['correct'] ?? ''));
            $others = self::asStringList($row['others'] ?? []);
            $choices = array_values(array_unique(array_filter(array_merge([$correct], $others))));
            $id = (string) ($i + 1);
            $blanks[] = ['id' => $id, 'choices' => $choices];
            $values[] = $correct;
            $tokens[] = '___'.$id.'___';
        }

        return [
            'passage_tokens' => $tokens,
            'blanks' => $blanks,
            'answer_key' => ['kind' => 'blanks', 'values' => $values],
        ];
    }

    private static function compileGapPassage(string $passage, array $answers): array
    {
        $parts = preg_split('/_{3,}/', $passage) ?: [$passage];
        $blankCount = max(0, count($parts) - 1);
        $tokens = [];
        $values = [];
        foreach ($parts as $i => $part) {
            foreach (preg_split('/\s+/', trim($part)) ?: [] as $word) {
                if ($word !== '') {
                    $tokens[] = $word;
                }
            }
            if ($i >= $blankCount) {
                continue;
            }
            $tokens[] = '___';
            $values[] = trim((string) ($answers[$i] ?? ''));
        }

        return ['tokens' => $tokens, 'values' => $values];
    }

    public static function passageFromTokens(array $tokens): string
    {
        return collect($tokens)->map(function ($token) {
            $token = (string) $token;
            if ($token === '___' || preg_match('/^___\d+___?$/', $token)) {
                return '_____';
            }

            return $token;
        })->implode(' ');
    }

    public static function blankCount(string $passage): int
    {
        return preg_match_all('/_{3,}/', $passage) ?: 0;
    }

    private static function asList(mixed $value): array
    {
        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') {
                return [];
            }
            $decoded = json_decode($trim, true);

            return is_array($decoded) ? array_values($decoded) : [];
        }

        return is_array($value) ? array_values($value) : [];
    }

    private static function asStringList(mixed $value): array
    {
        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', preg_split('/[,\\n]+/', $value) ?: [])));
        }
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                return trim((string) ($item['text'] ?? $item['word'] ?? $item['label'] ?? ''));
            }

            return trim((string) $item);
        }, $value), fn ($item) => $item !== ''));
    }

    private static function padRows(array $rows, int $needed, callable $blank): array
    {
        $needed = max(0, $needed);
        $rows = array_values($rows);
        while (count($rows) < $needed) {
            $rows[] = $blank();
        }
        if ($needed > 0 && count($rows) > $needed) {
            $rows = array_slice($rows, 0, $needed);
        }

        return $rows;
    }

    private static function truthy(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on';
    }
}
