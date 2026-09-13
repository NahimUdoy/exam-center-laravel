<?php

namespace App\Support\Cms;

use App\Models\SiteSetting;

class Site
{
    public static function defaults(): array
    {
        return [
            'academyName' => 'NextVerseSkill Academy',
            'brandName' => 'NextVerseSkill',
            'heroTitle' => 'Your exam Academy.',
            'heroSubtitle' => 'Free. Personal. Consistent.',
            'heroNote' => "Pick your path — then don't stop! ♥",
            'logoImage' => '',
            'loginBg' => '',
            'coursesEyebrow' => '4 tracks · 660+ free sets',
            'coursesTitle' => 'Explore Our Courses',
            'coursesSubtitle' => 'Pick your exam. Every module is free. Your journey is already open.',
            'ticker' => [
                'IELTS 200+', 'PTE 160+', 'MBA 155+', 'BBA 145+', 'Listening', 'Reading',
                'Writing', 'Speaking', 'Quantitative', 'Logical Reasoning', '100% Free', 'No paywall',
            ],
            'howSteps' => [
                ['id' => 'level', 'title' => 'Tell Us Your Level', 'body' => 'Quick friendly questions — about one minute.', 'color' => '#2563eb', 'soft' => '#dbeafe'],
                ['id' => 'plan', 'title' => 'Get Your Plan', 'body' => 'A clear weekly path matched to your spirit.', 'color' => '#10b981', 'soft' => '#d1fae5'],
                ['id' => 'practice', 'title' => 'Start Practicing', 'body' => 'Real exam patterns. Free drills. Every day.', 'color' => '#8b5cf6', 'soft' => '#ede9fe'],
                ['id' => 'track', 'title' => 'Track Your Progress', 'body' => 'See growth, keep momentum, earn certificates.', 'color' => '#f59e0b', 'soft' => '#fef3c7'],
            ],
            'freeItems' => [
                'All Practice Questions', 'All Mock Tests', 'Personalized Study Plans',
                'Progress Tracking', 'E-Certificates', 'All Exam Sections',
            ],
            'pillars' => [
                ['id' => 'level-check', 'icon' => 'Compass', 'title' => 'Level check', 'body' => 'Friendly questions. About one minute. No pressure theater.'],
                ['id' => 'roadmap', 'icon' => 'Target', 'title' => 'Personal roadmap', 'body' => 'Week-by-week goals, daily minute blocks, and total study time.'],
                ['id' => 'path', 'icon' => 'BookOpenCheck', 'title' => 'Free practice path', 'body' => 'Open drills, mocks, and certificates — everything free now.'],
            ],
            'academyHeroTitle' => 'Your exam Academy.',
            'academyHeroSubtitle' => 'Tell us your level. We prepare a full premium study plan — daily minutes, weekly rhythm, week-by-week goals, and your total use time across the whole journey.',
            'newsModal' => [
                'enabled' => true,
                'id' => 'welcome-2026-09',
                'eyebrow' => 'Welcome',
                'title' => "You're in. Let's make today count.",
                'body' => 'Your practice path is ready. We will also use this window for events, live classes, and academy news.',
                'cta_label' => 'Start practicing',
                'cta_url' => '/dashboard',
                'items' => [
                    ['title' => 'Practice & mocks', 'body' => 'Open any question or mock. Login is only needed when you start.'],
                    ['title' => 'Events & news', 'body' => 'Upcoming classes, exam dates, and academy updates will show here.'],
                    ['title' => '100% free for now', 'body' => 'Plans, certificates, and drills stay open while we grow with you.'],
                ],
            ],
        ];
    }

    public static function get(): array
    {
        $row = SiteSetting::query()->first();

        return array_replace_recursive(self::defaults(), $row?->payload ?? []);
    }

    public static function put(array $payload): array
    {
        $merged = array_replace_recursive(self::defaults(), $payload);
        $row = SiteSetting::query()->first() ?? new SiteSetting;
        $row->payload = $merged;
        $row->save();

        return $merged;
    }
}
