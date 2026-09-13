<?php

namespace App\Support;

use App\Models\PracticeQuestion;

class PracticePayload
{
    /** @var list<string> */
    private const DISTRACTORS = [
        'however', 'therefore', 'community', 'evidence', 'policy', 'research', 'access',
        'measure', 'sustain', 'budget', 'training', 'forecast', 'habitat', 'transit',
        'volunteer', 'archive', 'climate', 'literacy', 'inventory', 'schedule',
        'although', 'significant', 'regular', 'local', 'public', 'accurate',
    ];

    /** @var list<string> */
    private const FALSE_CLAIMS = [
        'The passage argues that one expensive campaign is better than daily habits.',
        'The writer says measurement is optional once a project has public support.',
        'The text claims night hours never matter for working residents.',
        'The author concludes that volunteers should replace trained staff.',
        'The passage states that forecasts are less useful than emergency borrowing.',
        'The writer recommends hiding unpaid invoices until the product is popular.',
    ];

    public static function hydrate(PracticeQuestion $item): array
    {
        $built = self::build($item);
        $stored = is_array($item->meta) ? $item->meta : [];
        foreach ($built as $key => $value) {
            if (self::filled($stored[$key] ?? null)) {
                $built[$key] = $stored[$key];
            }
        }

        $built['interaction'] = PracticeKind::mode($item->task_slug ?: 'read-aloud');
        $built['prompt'] = $item->prompt ?: ($built['prompt'] ?? '');
        $built['passage'] = $item->passage ?: ($built['passage'] ?? PracticeBank::PASSAGE);
        $built['image_url'] = $item->image_url ?: ($built['image_url'] ?? null);

        return $built;
    }

    public static function build(PracticeQuestion $item): array
    {
        $slug = $item->task_slug ?: 'read-aloud';
        $mode = PracticeKind::mode($slug);
        $kind = PracticeKind::for($slug);
        $seed = abs(crc32((string) $item->external_id.'|'.$slug));
        $passage = trim((string) ($item->passage ?: PracticeBank::PASSAGE));
        $title = $item->title ?: 'Practice item';
        $prompt = trim((string) ($item->prompt ?: ''));
        $base = [
            'interaction' => $mode,
            'prompt' => $prompt,
            'passage' => $passage,
            'audio_text' => null,
            'options' => [],
            'answer_key' => null,
            'correct_text' => null,
            'sample_answer' => null,
            'word_limit' => null,
            'blanks' => [],
            'passage_tokens' => [],
            'word_bank' => [],
            'paragraphs' => [],
            'display_order' => [],
            'transcript_with_errors' => [],
            'prep_seconds' => $kind['prep'],
            'record_seconds' => $kind['record'],
            'image_url' => $item->image_url,
        ];

        $base['sample_answer'] = self::spokenSample($title, $passage);

        return match ($mode) {
            'speak-read', 'speak-image', 'speak-prompt' => $base,
            'speak-audio' => self::withAudio($base, $slug, $title, $passage, $seed),
            'write' => self::withWriting($base, $slug, $title, $passage, $prompt),
            'mcq' => self::withMcq($base, $passage, $seed, false),
            'listen-mcq' => self::withMcq($base, $passage, $seed, true),
            'fib-select' => self::withFibSelect($base, $passage, $seed),
            'fib-bank' => self::withFibBank($base, $passage, $seed),
            'reorder' => self::withReorder($base, $passage, $seed),
            'listen-write' => self::withListenWrite($base, $slug, $passage),
            'hiw' => self::withHiw($base, $passage, $seed),
            default => $base,
        };
    }

    public static function mergeAdmin(array $meta, array $data, string $slug): array
    {
        $keep = array_intersect_key($meta, array_flip(['last_score', 'last_seconds', 'last_difficulty', 'last_answer']));
        $payload = [
            'interaction' => PracticeKind::mode($slug),
            'audio_text' => $data['audioText'] ?? ($meta['audio_text'] ?? null),
            'options' => array_values($data['options'] ?? ($meta['options'] ?? [])),
            'correct_text' => $data['correct_text'] ?? ($data['correctText'] ?? ($meta['correct_text'] ?? null)),
            'sample_answer' => $data['sampleAnswer'] ?? ($meta['sample_answer'] ?? null),
            'word_limit' => self::wordLimitFrom($data, $meta),
            'blanks' => $data['blanks'] ?? ($meta['blanks'] ?? []),
            'passage_tokens' => $data['passage_tokens'] ?? ($data['passageTokens'] ?? ($meta['passage_tokens'] ?? [])),
            'word_bank' => array_values($data['word_bank'] ?? ($data['wordBank'] ?? ($meta['word_bank'] ?? []))),
            'paragraphs' => $data['paragraphs'] ?? ($meta['paragraphs'] ?? []),
            'transcript_with_errors' => $data['transcript_with_errors'] ?? ($data['transcript'] ?? ($meta['transcript_with_errors'] ?? [])),
            'prep_seconds' => isset($data['prepSeconds']) ? (int) $data['prepSeconds'] : ($meta['prep_seconds'] ?? null),
            'record_seconds' => isset($data['recordSeconds']) ? (int) $data['recordSeconds'] : ($meta['record_seconds'] ?? null),
        ];

        $payload['answer_key'] = $data['answer_key'] ?? ($data['answerKey'] ?? ($meta['answer_key'] ?? null));
        if (! self::filled($payload['answer_key'])) {
            $payload['answer_key'] = self::answerKeyFromForm($data, $payload);
        }

        return array_merge($keep, array_filter($payload, fn ($v) => $v !== null && $v !== ''));
    }

    private static function withAudio(array $base, string $slug, string $title, string $passage, int $seed): array
    {
        $sentence = self::firstSentence($passage);
        $base['audio_text'] = match ($slug) {
            'answer-short' => 'What is the main idea of '.$title.'?',
            'repeat-sentence' => $sentence,
            default => $passage,
        };
        if ($slug === 'answer-short') {
            $base['correct_text'] = self::shortAnswer($title, $passage, $seed);
            $base['answer_key'] = ['kind' => 'text', 'value' => $base['correct_text']];
            $base['sample_answer'] = $base['correct_text'];
        } else {
            $base['sample_answer'] = $sentence;
        }

        return $base;
    }

    private static function withWriting(array $base, string $slug, string $title, string $passage, string $prompt): array
    {
        $base['prompt'] = $prompt !== '' ? $prompt : match ($slug) {
            'swt' => 'Summarize the passage in one sentence.',
            'essay' => 'Write an essay on “'.$title.'”. Give reasons and examples.',
            'task1' => 'Summarise the information in the source. Select and report the main features.',
            default => 'Analyse the case “'.$title.'”. Recommend one action and name one risk.',
        };
        $base['word_limit'] = match ($slug) {
            'swt' => ['min' => 5, 'max' => 75],
            'essay' => ['min' => 200, 'max' => 300],
            'task1' => ['min' => 150, 'max' => 180],
            default => ['min' => 120, 'max' => 250],
        };
        $base['sample_answer'] = self::firstSentence($passage).' '.self::secondSentence($passage);

        return $base;
    }

    private static function withMcq(array $base, string $passage, int $seed, bool $listening): array
    {
        $correct = 'The passage emphasises that '.self::claimFrom($passage);
        $options = self::seededShuffle(array_merge([$correct], array_slice(self::FALSE_CLAIMS, $seed % 3, 3)), $seed);
        $index = array_search($correct, $options, true);
        $base['prompt'] = $listening
            ? 'According to the talk, which statement is most accurate?'
            : 'What is the main claim of this passage?';
        $base['options'] = $options;
        $base['answer_key'] = ['kind' => 'single', 'index' => $index === false ? 0 : $index];
        $base['correct_text'] = $correct;
        if ($listening) {
            $base['audio_text'] = $passage;
        }

        return $base;
    }

    private static function withFibSelect(array $base, string $passage, int $seed): array
    {
        $tokens = preg_split('/\s+/', $passage) ?: [];
        $picks = self::blankIndexes($tokens, $seed, 3);
        $blanks = [];
        $values = [];
        $out = [];
        $n = 1;
        foreach ($tokens as $i => $token) {
            if (isset($picks[$i])) {
                $word = self::bare($token);
                $id = (string) $n;
                $choices = self::seededShuffle(array_merge([$word], self::distractorsFor($word, $seed + $n)), $seed + $n * 11);
                $blanks[] = ['id' => $id, 'choices' => $choices];
                $values[] = $word;
                $out[] = '___'.$id.'___';
                $n++;
            } else {
                $out[] = $token;
            }
        }
        $base['passage_tokens'] = $out;
        $base['blanks'] = $blanks;
        $base['answer_key'] = ['kind' => 'blanks', 'values' => $values];
        $base['correct_text'] = implode(' ', $values);

        return $base;
    }

    private static function withFibBank(array $base, string $passage, int $seed): array
    {
        $tokens = preg_split('/\s+/', $passage) ?: [];
        $picks = self::blankIndexes($tokens, $seed, 4);
        $values = [];
        $out = [];
        foreach ($tokens as $i => $token) {
            if (isset($picks[$i])) {
                $values[] = self::bare($token);
                $out[] = '___';
            } else {
                $out[] = $token;
            }
        }
        $extra = array_slice(self::DISTRACTORS, $seed % 8, 3);
        $base['passage_tokens'] = $out;
        $base['word_bank'] = self::seededShuffle(array_merge($values, $extra), $seed);
        $base['answer_key'] = ['kind' => 'blanks', 'values' => $values];
        $base['correct_text'] = implode(' ', $values);

        return $base;
    }

    private static function withReorder(array $base, string $passage, int $seed): array
    {
        $sentences = preg_split('/(?<=[.?!])\s+/', trim($passage)) ?: [$passage];
        $sentences = array_values(array_filter($sentences));
        if (count($sentences) < 3) {
            $sentences = [
                self::firstSentence($passage),
                'Staff who stay for several seasons keep quality higher than short campaigns.',
                'Clear measurement is what turns a good idea into a budget line.',
                self::secondSentence($passage) ?: 'A protected sleep window cuts errors more than extra caffeine.',
            ];
        }
        $sentences = array_slice($sentences, 0, 4);
        $paragraphs = [];
        $ids = [];
        foreach ($sentences as $i => $text) {
            $id = chr(97 + $i);
            $ids[] = $id;
            $paragraphs[] = ['id' => $id, 'text' => $text];
        }
        $base['paragraphs'] = $paragraphs;
        $base['display_order'] = self::seededShuffle($ids, $seed);
        if ($base['display_order'] === $ids) {
            $base['display_order'] = array_reverse($ids);
        }
        $base['answer_key'] = ['kind' => 'order', 'ids' => $ids];
        $base['correct_text'] = implode(' → ', $ids);

        return $base;
    }

    private static function withListenWrite(array $base, string $slug, string $passage): array
    {
        $sentence = self::firstSentence($passage);
        if ($slug === 'wfd') {
            $base['audio_text'] = $sentence;
            $base['correct_text'] = strtolower(rtrim($sentence, '.!? '));
            $base['answer_key'] = ['kind' => 'text', 'value' => $base['correct_text']];
            $base['word_limit'] = ['min' => 4, 'max' => 20];
        } else {
            $base['audio_text'] = $passage;
            $base['word_limit'] = ['min' => 50, 'max' => 70];
            $base['sample_answer'] = $sentence.' '.self::secondSentence($passage);
        }

        return $base;
    }

    private static function withHiw(array $base, string $passage, int $seed): array
    {
        $words = preg_split('/\s+/', self::firstSentence($passage).' '.self::secondSentence($passage)) ?: [];
        $indexes = array_keys(self::blankIndexes($words, $seed, 3));
        $swaps = ['libraries' => 'laboratories', 'community' => 'company', 'knowledge' => 'package', 'residents' => 'recipes', 'digital' => 'typical', 'accessible' => 'accidental', 'practical' => 'partial', 'quiet' => 'quite'];
        $incorrect = [];
        $transcript = [];
        foreach ($words as $i => $word) {
            $bare = strtolower(self::bare($word));
            $swap = $swaps[$bare] ?? null;
            if ($swap && in_array($i, $indexes, true)) {
                $incorrect[] = $swap;
                $transcript[] = ['word' => $swap, 'incorrect' => true];
            } else {
                $transcript[] = ['word' => $word, 'incorrect' => false];
            }
        }
        if ($incorrect === []) {
            $mid = (int) floor(count($transcript) / 2);
            $transcript[$mid]['word'] = 'unusual';
            $transcript[$mid]['incorrect'] = true;
            $incorrect[] = 'unusual';
        }
        $base['audio_text'] = $passage;
        $base['transcript_with_errors'] = $transcript;
        $base['answer_key'] = ['kind' => 'hiw', 'words' => $incorrect];
        $base['correct_text'] = implode(', ', $incorrect);

        return $base;
    }

    private static function answerKeyFromForm(array $data, array $payload): ?array
    {
        if (isset($data['correctIndex']) && $data['correctIndex'] !== '' && $data['correctIndex'] !== null) {
            return ['kind' => 'single', 'index' => (int) $data['correctIndex']];
        }
        if (! empty($data['correctIndexes'])) {
            return ['kind' => 'multi', 'indexes' => array_map('intval', (array) $data['correctIndexes'])];
        }
        if (! empty($payload['correct_text'])) {
            return ['kind' => 'text', 'value' => $payload['correct_text']];
        }

        return $payload['answer_key'] ?? null;
    }

    private static function wordLimitFrom(array $data, array $meta): ?array
    {
        $min = $data['wordLimitMin'] ?? ($meta['word_limit']['min'] ?? null);
        $max = $data['wordLimitMax'] ?? ($meta['word_limit']['max'] ?? null);
        if ($min === null && $max === null) {
            return $meta['word_limit'] ?? null;
        }

        return ['min' => (int) $min, 'max' => (int) $max];
    }

    private static function claimFrom(string $passage): string
    {
        $sentence = lcfirst(rtrim(self::firstSentence($passage), '. '));

        return $sentence.'.';
    }

    private static function shortAnswer(string $title, string $passage, int $seed): string
    {
        $bits = preg_split('/[:–-]/', $title) ?: [$title];
        $topic = strtolower(trim($bits[0]));
        $facts = ['regular local action', 'clear measurement', 'trained staff', 'shared equipment'];

        return $topic.' needs '.$facts[$seed % count($facts)];
    }

    private static function spokenSample(string $title, string $passage): string
    {
        return 'The topic is '.$title.'. '.self::firstSentence($passage);
    }

    private static function firstSentence(string $passage): string
    {
        $parts = preg_split('/(?<=[.?!])\s+/', trim($passage)) ?: [$passage];

        return trim($parts[0] ?? $passage);
    }

    private static function secondSentence(string $passage): string
    {
        $parts = preg_split('/(?<=[.?!])\s+/', trim($passage)) ?: [];

        return trim($parts[1] ?? 'Local staff who stay keep quality higher than one-off campaigns.');
    }

    private static function blankIndexes(array $tokens, int $seed, int $count): array
    {
        $candidates = [];
        foreach ($tokens as $i => $token) {
            if (strlen(self::bare($token)) >= 5 && $i > 0 && $i < count($tokens) - 1) {
                $candidates[] = $i;
            }
        }
        if ($candidates === []) {
            $candidates = array_keys($tokens);
        }
        $picked = array_slice(self::seededShuffle($candidates, $seed), 0, min($count, count($candidates)));
        sort($picked);

        return array_fill_keys($picked, true);
    }

    private static function distractorsFor(string $word, int $seed): array
    {
        $pool = array_values(array_filter(self::DISTRACTORS, fn ($w) => strtolower($w) !== strtolower($word)));

        return array_slice(self::seededShuffle($pool, $seed), 0, 3);
    }

    private static function seededShuffle(array $items, int $seed): array
    {
        $items = array_values($items);
        usort($items, function ($a, $b) use ($seed) {
            return crc32($seed.'|'.(is_scalar($a) ? (string) $a : serialize($a)))
                <=> crc32($seed.'|'.(is_scalar($b) ? (string) $b : serialize($b)));
        });

        return $items;
    }

    private static function bare(string $token): string
    {
        return preg_replace('/[^A-Za-z]/', '', $token) ?: $token;
    }

    private static function filled(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return false;
        }

        return true;
    }
}
