<?php

namespace App\Services;

class PlanBuilder
{
    public static function build(string $examSlug, string $feeling, array $diagnosticScores, int $daysPerWeek): array
    {
        $feelingScore = ['starting' => 0, 'building' => 2, 'ready' => 4][$feeling] ?? 2;
        $diag = count($diagnosticScores)
            ? array_sum($diagnosticScores) / count($diagnosticScores)
            : 1;
        $total = $feelingScore + $diag;

        $tier = 'foundation';
        if ($total >= 5) {
            $tier = 'intensive';
        } elseif ($total >= 3) {
            $tier = 'steady';
        }

        $days = max(3, min(7, $daysPerWeek));
        $examName = strtoupper($examSlug === 'english-basic' ? 'Basics' : $examSlug);

        $meta = [
            'foundation' => [
                'level_label' => 'Foundation builder',
                'headline' => "Your {$examName} journey starts gentle — and stays consistent.",
                'summary' => 'We keep sessions short so you never burn out. Show up on planned days, finish small wins, and watch confidence return.',
                'weeks' => 6,
                'daily_minutes' => 25,
                'weekend_minutes' => 20,
            ],
            'steady' => [
                'level_label' => 'Steady climber',
                'headline' => 'You already have the spark — this plan protects it.',
                'summary' => 'You know enough to grow fast. We balance drills and sectional mocks so progress feels visible every week.',
                'weeks' => 5,
                'daily_minutes' => 40,
                'weekend_minutes' => 45,
            ],
            'intensive' => [
                'level_label' => 'Exam-ready push',
                'headline' => 'Your thinking is sharp — now we lock the routine.',
                'summary' => 'You are close. We tighten timing, full mocks, and targeted fixes so exam day feels familiar.',
                'weeks' => 4,
                'daily_minutes' => 55,
                'weekend_minutes' => 90,
            ],
        ][$tier];

        $weekly = $meta['daily_minutes'] * $days + $meta['weekend_minutes'];
        $totalMinutes = $weekly * $meta['weeks'];

        $skills = match ($examSlug) {
            'pte' => ['Speaking', 'Writing', 'Reading', 'Listening'],
            'ielts' => ['Listening', 'Reading', 'Writing', 'Speaking'],
            'bba' => ['English', 'Math', 'Logical Reasoning', 'Business Awareness'],
            'mba' => ['Quantitative', 'Verbal', 'Data Interpretation', 'Logical Reasoning'],
            default => ['Grammar', 'Vocabulary', 'Reading', 'Speaking fluency'],
        };

        $sessionBlocks = [
            ['title' => 'Core skill warm-up', 'minutes' => (int) round($meta['daily_minutes'] * 0.55), 'detail' => 'Rotate '.$skills[0].' → '.$skills[1].' across the week.'],
            ['title' => 'Timed micro-set', 'minutes' => (int) round($meta['daily_minutes'] * 0.35), 'detail' => 'One timed set. Score it. Log 1 mistake.'],
            ['title' => 'Close the loop', 'minutes' => max(2, $meta['daily_minutes'] - (int) round($meta['daily_minutes'] * 0.55) - (int) round($meta['daily_minutes'] * 0.35)), 'detail' => 'Write one win + one fix before you leave.'],
        ];

        $dayTemplates = [];
        for ($i = 0; $i < $days; $i++) {
            $dayTemplates[] = [
                'day_label' => 'Day '.($i + 1),
                'title' => $skills[$i % count($skills)].' focus',
                'minutes' => $meta['daily_minutes'],
                'blocks' => [
                    $skills[$i % count($skills)].' warm-up',
                    'Timed micro-set',
                    'Error log note',
                ],
            ];
        }

        $weekTitles = [
            'foundation' => ['Arrive & feel safe', 'Build the habit', 'Skill clarity', 'Gentle speed', 'Confidence return', 'Foundation lock'],
            'steady' => ['Diagnose & target', 'Weak-skill repair', 'Pattern mastery', 'Balance & stamina', 'Score visibility'],
            'intensive' => ['Exam simulation starts', 'Kill recurring errors', 'Peak polish', 'Exam-ready lock'],
        ][$tier];

        $weekPlan = [];
        foreach ($weekTitles as $i => $title) {
            $weekPlan[] = [
                'week' => $i + 1,
                'title' => $title,
                'goal' => "Stay consistent on {$examName} — protect energy, finish planned days.",
                'focus_skills' => $skills,
                'daily_focus' => 'Warm-up + micro-set + close loop',
                'weekend_focus' => 'Light mock review / error redo ('.$meta['weekend_minutes'].' min)',
                'minutes_this_week' => $weekly,
            ];
        }

        return [
            'tier' => $tier,
            'exam_slug' => $examSlug,
            'exam_name' => $examName,
            'level_label' => $meta['level_label'],
            'headline' => $meta['headline'],
            'summary' => $meta['summary'],
            'weeks' => $meta['weeks'],
            'daily_minutes' => $meta['daily_minutes'],
            'days_per_week' => $days,
            'weekend_minutes' => $meta['weekend_minutes'],
            'weekly_minutes' => $weekly,
            'total_minutes' => $totalMinutes,
            'total_hours_label' => self::formatHours($totalMinutes),
            'session_blocks' => $sessionBlocks,
            'day_templates' => $dayTemplates,
            'week_plan' => $weekPlan,
            'milestones' => [
                'Week 2: planned days completed without quitting',
                'Mid-plan: weakest skill identified',
                'Final week: ready for the next timed mock',
            ],
            'consistency_rules' => [
                'Never skip two planned days in a row — do a 10-min rescue session instead.',
                'Always end with one written win so your brain remembers progress.',
                'If energy is low: warm-up only still counts.',
                'Total commitment: '.self::formatHours($totalMinutes).' across '.$meta['weeks'].' weeks.',
            ],
            'first_action' => [
                'label' => $tier === 'intensive' ? "Start free {$examName} mock" : "Open free {$examName} practice",
                'href' => $tier === 'intensive' ? '/mock-tests?exam='.$examSlug : '/dashboard?exam='.$examSlug,
            ],
        ];
    }

    private static function formatHours(int $totalMinutes): string
    {
        $hours = $totalMinutes / 60;
        if ($hours >= 10) {
            return number_format($hours, 1).' hours';
        }
        $h = intdiv($totalMinutes, 60);
        $m = $totalMinutes % 60;
        if ($h === 0) {
            return $m.' minutes';
        }
        if ($m === 0) {
            return $h.' hours';
        }

        return "{$h}h {$m}m";
    }
}
