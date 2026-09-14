<?php

namespace App\Support;

use App\Models\Exam;
use App\Models\PracticeQuestion;
use Illuminate\Support\Collection;

class SpeakingScript
{
    public static function forExam(Exam $exam): array
    {
        $interviews = self::interviewBank($exam);
        $cue = self::cueCard($exam);
        $part3 = self::partThree($cue);

        $steps = [];
        $steps[] = [
            'id' => 'greet',
            'phase' => 'greet',
            'listenOnly' => true,
            'prep' => 0,
            'record' => 0,
            'text' => self::greeting($exam),
            'bullets' => [],
        ];

        foreach ($interviews->take(5)->values() as $i => $item) {
            $steps[] = [
                'id' => 'part1-'.($i + 1),
                'phase' => 'part1',
                'listenOnly' => false,
                'prep' => 0,
                'record' => 45,
                'text' => $item['text'],
                'bullets' => [],
            ];
        }

        $steps[] = [
            'id' => 'follow',
            'phase' => 'follow',
            'listenOnly' => false,
            'dynamic' => true,
            'prep' => 0,
            'record' => 40,
            'text' => 'Can you give one more example so I understand you clearly?',
            'bullets' => [],
        ];

        $steps[] = [
            'id' => 'part2',
            'phase' => 'part2',
            'listenOnly' => false,
            'prep' => 60,
            'record' => 120,
            'text' => $cue['text'],
            'bullets' => $cue['bullets'],
        ];

        foreach ($part3 as $i => $text) {
            $steps[] = [
                'id' => 'part3-'.($i + 1),
                'phase' => 'part3',
                'listenOnly' => false,
                'prep' => 0,
                'record' => 50,
                'text' => $text,
                'bullets' => [],
            ];
        }

        return [
            'exam' => [
                'slug' => $exam->slug,
                'name' => $exam->name,
                'short_name' => $exam->short_name,
            ],
            'minutes' => '10–14',
            'bank' => [
                'interviews' => $interviews->unique('text')->count(),
                'cueCards' => PracticeQuestion::query()->where('external_id', 'like', 'nvs-speak-cue-%')->count()
                    ?: count(SpeakingBank::cueCards($exam->slug)),
            ],
            'steps' => $steps,
            'followUps' => self::followUps(),
            'saveUrl' => route('speaking.store', ['exam' => $exam->slug]),
        ];
    }

    public static function score(array $turns, int $seconds): array
    {
        $student = collect($turns)->where('role', 'student');
        $words = $student->sum(fn ($turn) => str_word_count((string) ($turn['text'] ?? '')));
        $answered = $student->filter(fn ($turn) => trim((string) ($turn['text'] ?? '')) !== '')->count();
        $spokenSeconds = (int) $student->sum(fn ($turn) => (int) ($turn['seconds'] ?? 0));
        $hash = abs(crc32($words.'|'.$seconds.'|'.$answered));
        $base = 62 + ($hash % 12);

        $lengthBonus = match (true) {
            $spokenSeconds >= 90 && $words >= 80 => 16,
            $spokenSeconds >= 50 && $words >= 40 => 10,
            $spokenSeconds >= 20 && $words >= 16 => 4,
            $spokenSeconds > 0 => -6,
            default => -20,
        };
        $answerBonus = min(8, $answered * 2);

        $content = self::clamp($base + $lengthBonus + (int) min(8, $words / 20));
        $pronunciation = self::clamp($base + 4 + (int) ($lengthBonus / 2) + (($hash >> 3) % 6));
        $fluency = self::clamp($base + $lengthBonus + $answerBonus);
        $intonation = self::clamp($base + 2 + (($hash >> 5) % 7) + (int) ($lengthBonus / 3));
        $overall = (int) round(($content + $pronunciation + $fluency + $intonation) / 4);

        return [
            'overall' => $overall,
            'traits' => [
                ['label' => 'Content', 'score' => $content],
                ['label' => 'Pronunciation', 'score' => $pronunciation],
                ['label' => 'Fluency', 'score' => $fluency],
                ['label' => 'Intonation', 'score' => $intonation],
            ],
            'band' => match (true) {
                $overall >= 85 => 'Strong attempt',
                $overall >= 75 => 'Solid practice score',
                $overall >= 60 => 'Keep drilling this session',
                default => 'Incomplete delivery',
            },
            'tips' => [
                $spokenSeconds < 40
                    ? 'Answer in fuller turns. A reason plus one example lifts fluency and content.'
                    : 'Hold a steady pace and finish the last sentence cleanly.',
                $words < 40
                    ? 'Add a short example after each opinion so the examiner can follow you.'
                    : 'You covered enough language. Next time extend one Part 3 answer.',
            ],
            'note' => 'Practice score from how long you spoke and how complete each turn was. This is free in-browser scoring, not an official exam mark.',
            'answered' => $answered,
            'words' => $words,
            'spoken_seconds' => $spokenSeconds,
        ];
    }

    public static function followUps(): array
    {
        return SpeakingBank::followUps();
    }

    private static function greeting(Exam $exam): string
    {
        $name = $exam->short_name;

        return "Hello. I am your free {$name} speaking examiner today. This is a one-to-one practice interview, not the official test. I will ask several warm-up questions, then give you a cue card, then three discussion questions. Speak naturally. When you finish an answer, tap Done.";
    }

    private static function interviewBank(Exam $exam): Collection
    {
        $fromDb = PracticeQuestion::query()
            ->where('task_slug', 'interview')
            ->where('external_id', 'like', 'nvs-speak-int-%')
            ->get()
            ->map(fn (PracticeQuestion $q) => [
                'text' => $q->prompt ?: $q->title,
            ])
            ->filter(fn ($row) => filled($row['text']));

        $builtIn = collect(SpeakingBank::interviews($exam->slug))
            ->map(fn (string $text) => ['text' => $text]);

        return $fromDb->concat($builtIn)->unique('text')->shuffle()->values();
    }

    private static function cueCard(Exam $exam): array
    {
        $fromDb = PracticeQuestion::query()
            ->where('task_slug', 'cue-card')
            ->where('external_id', 'like', 'nvs-speak-cue-%')
            ->get()
            ->map(function (PracticeQuestion $q) {
                $meta = is_array($q->meta) ? $q->meta : [];

                return [
                    'topic' => $q->title,
                    'text' => $q->prompt ?: $q->title,
                    'bullets' => $meta['bullets'] ?? self::bullets($q->prompt ?: $q->passage ?: $q->title),
                    'tags' => $meta['tags'] ?? [],
                ];
            });

        $all = $fromDb->concat(SpeakingBank::cueCards($exam->slug))->unique('topic')->shuffle();
        $card = $all->first();

        return $card ?: SpeakingBank::cueCards($exam->slug)[0];
    }

    /**
     * @param  array{topic?: string, tags?: list<string>}  $cue
     * @return list<string>
     */
    private static function partThree(array $cue): array
    {
        $pools = SpeakingBank::partThreePools();
        $picked = collect();

        foreach ($cue['tags'] ?? [] as $tag) {
            $picked = $picked->concat($pools[$tag] ?? []);
        }

        $hay = strtolower((string) ($cue['topic'] ?? ''));
        foreach ([
            'stud' => 'study', 'learn' => 'study', 'school' => 'study', 'book' => 'study',
            'job' => 'work', 'team' => 'work', 'office' => 'work',
            'friend' => 'people', 'family' => 'people',
            'city' => 'place', 'park' => 'place', 'shop' => 'place', 'building' => 'place',
            'visit' => 'travel', 'country' => 'travel',
            'phone' => 'tech', 'app' => 'tech', 'online' => 'tech', 'website' => 'tech',
            'sport' => 'health', 'habit' => 'health',
            'gift' => 'object', 'photo' => 'object',
            'celebrat' => 'event', 'meal' => 'event', 'late' => 'event',
            'english' => 'skill', 'advice' => 'skill',
        ] as $needle => $pool) {
            if (str_contains($hay, $needle)) {
                $picked = $picked->concat($pools[$pool] ?? []);
            }
        }

        return $picked
            ->concat($pools['default'])
            ->unique()
            ->shuffle()
            ->take(3)
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private static function bullets(string $prompt): array
    {
        if (preg_match('/you should say:?\s*(.+)$/i', $prompt, $match)) {
            $chunk = preg_replace('/\band\s+explain\b/i', ',', $match[1]);
            $parts = preg_split('/,\s*|\band\s+/i', (string) $chunk) ?: [];

            return collect($parts)
                ->map(fn ($part) => trim((string) $part, " ."))
                ->filter()
                ->take(4)
                ->values()
                ->all();
        }

        return ['Say what it is', 'Give one detail', 'Give one example', 'Explain why it matters'];
    }

    private static function clamp(int $value): int
    {
        return max(35, min(96, $value));
    }
}
