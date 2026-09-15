<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\CertificateCategory;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\DiagnosticQuestion;
use App\Models\Exam;
use App\Models\MediaItem;
use App\Models\MockPassage;
use App\Models\MockQuestion;
use App\Models\MockSection;
use App\Models\MockTest;
use App\Models\PracticeQuestion;
use App\Models\PteQuestion;
use App\Models\PteTaskType;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\TaskType;
use App\Models\User;
use App\Support\Cms\Site;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')
            && Exam::query()->exists()
            && PracticeQuestion::query()->count() > 100) {
            $this->command?->info('Catalog already loaded — adding study packs only.');
            User::query()->updateOrCreate(
                ['email' => 'student@nextverseskill.test'],
                [
                    'name' => 'NV Student',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $this->call(PracticeContentSeeder::class);

            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'student@nextverseskill.test'],
            [
                'name' => 'NV Student',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $data = fn (string $file) => json_decode(
            file_get_contents(database_path("data/{$file}")),
            true
        );

        $examIcons = [
            'pte' => 'Languages',
            'ielts' => 'GraduationCap',
            'bba' => 'Briefcase',
            'mba' => 'Briefcase',
            'english-basic' => 'BookOpen',
        ];
        foreach ($data('exams.json') as $row) {
            $row['icon'] = $examIcons[$row['slug']] ?? 'GraduationCap';
            Exam::query()->updateOrCreate(['slug' => $row['slug']], $row);
        }

        PracticeQuestion::query()->delete();
        TaskType::query()->delete();
        foreach ($data('task_types.json') as $row) {
            TaskType::query()->create($row);
        }

        $readAloud = TaskType::query()->where('slug', 'read-aloud')->first();
        foreach ($data('practice_questions.json') as $row) {
            PracticeQuestion::query()->create([
                'external_id' => $row['external_id'],
                'task_type_id' => $readAloud?->id,
                'skill' => 'speaking',
                'task_slug' => 'read-aloud',
                'title' => $row['title'],
                'difficulty' => $row['difficulty'],
                'appeared' => $row['appeared'],
                'is_prediction' => $row['is_prediction'],
                'practiced' => $row['practiced'] ?? false,
                'bookmarked' => $row['bookmarked'] ?? false,
                'prompt' => \App\Support\PracticeBank::PASSAGE,
                'passage' => \App\Support\PracticeBank::PASSAGE,
                'exam_slugs' => ['pte', 'ielts'],
            ]);
        }

        MockTest::query()->delete();
        foreach ($data('mock_tests.json') as $row) {
            MockTest::query()->create($row);
        }

        $readingMock = MockTest::query()->where('slug', 'ielts-r-rising-sum')->first()
            ?? MockTest::query()->where('exam_slug', 'ielts')->where('section', 'reading')->first();

        if ($readingMock) {
            foreach ($data('reading_passages.json') as $i => $row) {
                MockPassage::query()->create([
                    'mock_test_id' => $readingMock->id,
                    'label' => $row['label'],
                    'text' => $row['text'],
                    'sort_order' => $i,
                ]);
            }
            foreach ($data('reading_questions.json') as $row) {
                MockQuestion::query()->create([
                    'mock_test_id' => $readingMock->id,
                    'number' => $row['number'] ?? $row['id'],
                    'part' => $row['part'],
                    'prompt' => $row['prompt'],
                    'options' => $row['options'],
                    'correct_index' => $row['correct_index'],
                    'skill_tag' => $row['skill_tag'] ?? null,
                    'tip' => $row['tip'] ?? null,
                ]);
            }

            // Extra IELTS reading bank attached to Cambridge mock
            $extra = MockTest::query()->where('slug', 'ielts-r-cam08-t4')->first();
            if ($extra) {
                foreach (range(1, 10) as $n) {
                    MockQuestion::query()->create([
                        'mock_test_id' => $extra->id,
                        'number' => $n,
                        'part' => $n <= 4 ? 1 : ($n <= 7 ? 2 : 3),
                        'prompt' => "Extra Reading Q{$n}: Which option best matches the passage evidence?",
                        'options' => [
                            'Option A — unsupported claim',
                            'Option B — evidence-based answer',
                            'Option C — extreme absolute',
                            'Option D — off-topic detail',
                        ],
                        'correct_index' => 1,
                        'skill_tag' => 'Detail / evidence',
                        'tip' => 'Eliminate absolutes and match the paragraph evidence.',
                    ]);
                }
                foreach (['A', 'B', 'C'] as $i => $label) {
                    MockPassage::query()->create([
                        'mock_test_id' => $extra->id,
                        'label' => $label,
                        'text' => "Cambridge-style passage block {$label}. Focus on thrift, recovery, and modern investing themes for practice.",
                        'sort_order' => $i,
                    ]);
                }
            }
        }

        // Extra BBA/MBA MCQ banks on full mocks
        foreach (['bba-full-01', 'mba-full-01'] as $slug) {
            $mock = MockTest::query()->where('slug', $slug)->first();
            if (! $mock) {
                continue;
            }
            foreach (range(1, 15) as $n) {
                MockQuestion::query()->create([
                    'mock_test_id' => $mock->id,
                    'number' => $n,
                    'part' => 1,
                    'prompt' => strtoupper(substr($slug, 0, 3))." Aptitude Q{$n}: Choose the correct answer.",
                    'options' => ['12', '24', '36', '48'],
                    'correct_index' => ($n % 4),
                    'skill_tag' => $slug === 'bba-full-01' ? 'Quant' : 'Critical Reasoning',
                    'tip' => 'Work carefully; eliminate impossible options first.',
                ]);
            }
        }

        $describeImages = [
            'di-1' => '/uploads/di-screen-time.svg',
            'di-2' => '/uploads/di-recycled-paper.svg',
            'di-3' => '/uploads/di-campus-map.svg',
        ];
        PteQuestion::query()->delete();
        foreach ($data('pte_questions.json') as $i => $row) {
            $payload = $row['payload'] ?? [];
            $image = $describeImages[$row['external_id']] ?? null;
            if ($image) {
                $payload['image_url'] = $image;
            }
            PteQuestion::query()->create([
                'external_id' => $row['external_id'],
                'type' => $row['type'],
                'section' => $row['section'],
                'title' => $row['title'],
                'instructions' => $row['instructions'],
                'payload' => $payload,
                'image_url' => $image,
                'correct_text' => $row['correct_text'] ?? null,
                'answer_key' => $row['answer_key'] ?? null,
                'sort_order' => $i,
            ]);
        }

        Certificate::query()->delete();
        foreach ($data('certificates.json') as $row) {
            Certificate::query()->create($row);
        }

        DiagnosticQuestion::query()->delete();
        foreach ($data('diagnostics.json') as $i => $row) {
            DiagnosticQuestion::query()->create([
                'exam_slug' => $row['exam_slug'],
                'external_id' => $row['external_id'],
                'prompt' => $row['prompt'],
                'options' => $row['options'],
                'sort_order' => $i,
            ]);
        }

        Skill::query()->delete();
        foreach ($data('skills.json') as $row) {
            Skill::query()->create($row);
        }

        CourseModule::query()->delete();
        Course::query()->delete();
        foreach ($data('courses.json') as $row) {
            $modules = $row['modules'] ?? [];
            unset($row['modules']);
            $course = Course::query()->create($row);
            foreach ($modules as $i => $mod) {
                CourseModule::query()->create([
                    'course_id' => $course->id,
                    'name' => $mod['name'],
                    'count_label' => $mod['count'],
                    'value' => $mod['value'],
                    'icon' => $mod['icon'] ?? null,
                    'sort_order' => $i,
                ]);
            }
        }

        MockSection::query()->delete();
        foreach ($data('mock_sections.json') as $row) {
            MockSection::query()->create($row);
        }

        CertificateCategory::query()->delete();
        foreach ($data('cert_categories.json') as $row) {
            CertificateCategory::query()->create($row);
        }

        PteTaskType::query()->delete();
        foreach ($data('pte_task_types.json') as $row) {
            PteTaskType::query()->create($row);
        }

        SiteSetting::query()->delete();
        SiteSetting::query()->create(['payload' => Site::defaults()]);

        MediaItem::query()->delete();
        foreach ([
            ['di-screen-time', 'di-screen-time.svg', 'Average daily screen time by age group'],
            ['di-recycled-paper', 'di-recycled-paper.svg', 'How recycled paper is produced'],
            ['di-campus-map', 'di-campus-map.svg', 'Campus map changes 2000–2020'],
        ] as $media) {
            MediaItem::query()->create([
                'external_id' => $media[0],
                'url' => '/uploads/'.$media[1],
                'name' => $media[1],
                'alt' => $media[2],
                'size' => 0,
                'mime' => 'image/svg+xml',
            ]);
        }

        $this->call(PracticeContentSeeder::class);
        $this->call(EnglishBasicsSeeder::class);
    }
}
