<?php

namespace App\Support;

class PracticeKind
{
    public static function mode(string $slug): string
    {
        return match ($slug) {
            'read-aloud' => 'speak-read',
            'repeat-sentence', 'retell-lecture', 'answer-short' => 'speak-audio',
            'describe-image' => 'speak-image',
            'cue-card', 'interview' => 'speak-prompt',
            'swt', 'essay', 'task1', 'case-write' => 'write',
            'rc-mcq' => 'mcq',
            'rw-fib' => 'fib-select',
            'r-fib' => 'fib-bank',
            'reorder' => 'reorder',
            'sst', 'wfd' => 'listen-write',
            'l-mcq' => 'listen-mcq',
            'hiw' => 'hiw',
            default => 'speak-read',
        };
    }

    public static function for(string $slug): array
    {
        $mode = self::mode($slug);
        [$prep, $record] = match ($slug) {
            'repeat-sentence' => [3, 15],
            'describe-image' => [25, 40],
            'retell-lecture' => [10, 40],
            'answer-short' => [3, 10],
            'cue-card' => [60, 120],
            'interview' => [5, 45],
            default => [30, 35],
        };

        return [
            'slug' => $slug,
            'mode' => $mode,
            'instructions' => self::instructions($slug),
            'prep' => $prep,
            'record' => $record,
            'uses_mic' => str_starts_with($mode, 'speak-'),
            'uses_audio' => in_array($mode, ['speak-audio', 'listen-write', 'listen-mcq', 'hiw'], true),
            'uses_text' => in_array($mode, ['write', 'listen-write'], true),
            'scored' => in_array($mode, ['mcq', 'listen-mcq', 'fib-select', 'fib-bank', 'reorder', 'hiw', 'listen-write'], true)
                || $slug === 'answer-short',
            'tips' => self::tips($mode),
        ];
    }

    public static function instructions(string $slug): string
    {
        return match ($slug) {
            'read-aloud' => 'Look at the text below. In 35 seconds, read it aloud as naturally as possible.',
            'repeat-sentence' => 'You will hear a sentence. When the audio finishes, repeat it exactly.',
            'describe-image' => 'Look at the image. After preparation time, describe the main features and a comparison.',
            'retell-lecture' => 'Listen to the lecture. Then retell the key points in your own words.',
            'answer-short' => 'Listen to the question. Answer in a few words.',
            'cue-card' => 'You have one minute to prepare. Then speak for up to two minutes on the topic.',
            'interview' => 'Answer the interview question clearly. Give a reason and one example.',
            'swt' => 'Read the passage. Summarize it in one sentence. Check the word limit.',
            'essay' => 'Write an essay on the prompt. Stay on topic and use the word limit.',
            'task1' => 'Describe the key information in the source. Do not give unsupported opinions.',
            'case-write' => 'Analyse the case. State the issue, your recommendation, and one risk.',
            'rw-fib' => 'Choose the best word for each blank from the dropdown.',
            'r-fib' => 'Fill each blank using the word bank. Each word is used at most once.',
            'reorder' => 'Put the paragraphs in the most logical order.',
            'rc-mcq' => 'Read the passage and choose the best answer.',
            'sst' => 'Listen to the audio. Write a short summary of what you heard.',
            'wfd' => 'Listen to the sentence. Type it exactly as you hear it.',
            'l-mcq' => 'Listen to the audio, then choose the best answer.',
            'hiw' => 'Listen and click the words in the transcript that do not match the audio.',
            default => 'Read the prompt and complete the task.',
        };
    }

    public static function tips(string $mode): array
    {
        return match (true) {
            str_starts_with($mode, 'speak-') => [
                'AI scoring is available once you submit your response.',
                'Use a headset for a clearer speaking signal.',
            ],
            $mode === 'write' => [
                'Stay inside the word limit. Extra words do not raise the score.',
                'Plan one main idea, then write. Do not paste an unrelated essay.',
            ],
            in_array($mode, ['mcq', 'listen-mcq', 'fib-select', 'fib-bank', 'reorder', 'hiw'], true) => [
                'These items have a stored answer key. Submit to see accuracy.',
                'Eliminate options that contradict a specific sentence in the source.',
            ],
            default => [
                'Play the audio once, then type. Spelling and word order both count.',
                'If you miss a word, keep going — a complete sentence still scores.',
            ],
        };
    }
}
