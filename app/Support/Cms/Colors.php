<?php

namespace App\Support\Cms;

class Colors
{
    public const PRESETS = [
        ['label' => 'Brand blue', 'value' => '#2563eb'],
        ['label' => 'Deep blue', 'value' => '#1d4ed8'],
        ['label' => 'Ink', 'value' => '#0b1f44'],
        ['label' => 'Teal', 'value' => '#0d9488'],
        ['label' => 'Coral', 'value' => '#f06a4e'],
        ['label' => 'Rose', 'value' => '#f43f5e'],
        ['label' => 'Amber', 'value' => '#f59e0b'],
        ['label' => 'Emerald', 'value' => '#10b981'],
        ['label' => 'Violet', 'value' => '#8b5cf6'],
        ['label' => 'Purple', 'value' => '#7c3aed'],
        ['label' => 'Sky', 'value' => '#0ea5e9'],
        ['label' => 'Orange', 'value' => '#ea580c'],
        ['label' => 'Red', 'value' => '#ef4444'],
        ['label' => 'Gold', 'value' => '#c4a35a'],
    ];

    public const ICONS = [
        'GraduationCap' => 'Graduation',
        'BookOpen' => 'Book',
        'BookOpenCheck' => 'Study check',
        'Headphones' => 'Listening',
        'Mic2' => 'Speaking',
        'PenLine' => 'Writing',
        'Languages' => 'Languages',
        'Calculator' => 'Quant',
        'PieChart' => 'Data',
        'Sigma' => 'Math',
        'Brain' => 'Reasoning',
        'Briefcase' => 'Business',
        'Award' => 'Award',
        'Trophy' => 'Trophy',
        'Layers' => 'Modules',
        'Sparkles' => 'Sparkles',
        'Target' => 'Target',
        'Compass' => 'Compass',
    ];

    public static function parseHex(string $hex): array
    {
        $raw = ltrim(trim($hex), '#');
        if (strlen($raw) === 3) {
            $raw = $raw[0].$raw[0].$raw[1].$raw[1].$raw[2].$raw[2];
        }
        $raw = str_pad(substr($raw, 0, 6), 6, '0');
        $n = hexdec($raw);

        return [
            'r' => ($n >> 16) & 255,
            'g' => ($n >> 8) & 255,
            'b' => $n & 255,
        ];
    }

    public static function toHex(int $r, int $g, int $b): string
    {
        $h = fn (int $n) => str_pad(dechex(max(0, min(255, $n))), 2, '0', STR_PAD_LEFT);

        return '#'.$h($r).$h($g).$h($b);
    }

    public static function softFromHex(string $hex): string
    {
        ['r' => $r, 'g' => $g, 'b' => $b] = self::parseHex($hex);

        return self::toHex(
            (int) round($r + (255 - $r) * 0.86),
            (int) round($g + (255 - $g) * 0.86),
            (int) round($b + (255 - $b) * 0.86),
        );
    }

    public static function glowFromHex(string $hex, float $alpha = 0.45): string
    {
        ['r' => $r, 'g' => $g, 'b' => $b] = self::parseHex($hex);

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }
}
