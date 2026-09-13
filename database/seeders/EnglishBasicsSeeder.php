<?php

namespace Database\Seeders;

use App\Models\EnglishDrill;
use App\Models\EnglishLesson;
use App\Support\EnglishBasics\Catalog;
use Illuminate\Database\Seeder;

class EnglishBasicsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Catalog::lessons() as $row) {
            $drills = $row['drills'];
            unset($row['drills']);

            $lesson = EnglishLesson::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row
            );

            $lesson->drills()->delete();

            foreach ($drills as $drill) {
                EnglishDrill::query()->create([
                    'english_lesson_id' => $lesson->id,
                    ...$drill,
                ]);
            }
        }
    }
}
