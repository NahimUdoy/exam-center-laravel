<?php

namespace Database\Seeders;

use App\Models\MockPassage;
use App\Models\MockQuestion;
use App\Models\MockTest;
use App\Models\PracticeQuestion;
use App\Models\PteQuestion;
use App\Models\TaskType;
use App\Support\PracticeKind;
use App\Support\PracticePayload;
use App\Support\PteBank;
use Illuminate\Database\Seeder;

class PracticeContentSeeder extends Seeder
{
    private const PRACTICE_PER_TASK = 300;

    public function run(): void
    {
        $this->seedPracticeQuestions();
        $this->backfillPracticePayloads();
        $this->seedTypedShowcase();
        $this->seedExtraMocks();
        $this->seedMockBanks();
        $this->seedExtraPte();
        $this->refreshCounts();
    }

    private function seedPracticeQuestions(): void
    {
        $tasks = TaskType::query()->where('skill', '!=', 'mock')->orderBy('id')->get();
        $titles = $this->titles();
        $passages = $this->passages();
        $levels = ['Easy', 'Medium', 'Difficult'];
        $images = [
            '/uploads/di-screen-time.svg',
            '/uploads/di-recycled-paper.svg',
            '/uploads/di-campus-map.svg',
        ];

        foreach ($tasks as $ti => $task) {
            $existing = PracticeQuestion::query()->where('task_slug', $task->slug)->count();
            $needed = max(0, self::PRACTICE_PER_TASK - $existing);

            for ($n = 0; $n < $needed; $n++) {
                $seq = $existing + $n;
                $title = $this->titleFor($ti, $seq, $titles);
                $passage = $this->passageFor($ti, $seq, $passages);
                $prompt = $this->promptFor($task, $title, $passage);

                $row = PracticeQuestion::query()->create([
                    'external_id' => (string) (2000000 + ($ti * 10000) + $seq + 1),
                    'task_type_id' => $task->id,
                    'skill' => $task->skill,
                    'task_slug' => $task->slug,
                    'title' => $title,
                    'difficulty' => $levels[$seq % 3],
                    'appeared' => 8 + (($ti * 11 + $seq * 5) % 84),
                    'is_prediction' => $seq % 5 === 0,
                    'practiced' => $seq < min(4, (int) $task->done),
                    'bookmarked' => $seq === 2,
                    'prompt' => $prompt,
                    'passage' => $passage,
                    'image_url' => $task->slug === 'describe-image' ? $images[$seq % 3] : null,
                    'exam_slugs' => $task->exam_slugs ?? ['pte'],
                ]);
                $row->meta = PracticePayload::build($row);
                $row->save();
            }
        }
    }

    private function backfillPracticePayloads(): void
    {
        PracticeQuestion::query()->orderBy('id')->chunkById(150, function ($rows) {
            foreach ($rows as $row) {
                $meta = is_array($row->meta) ? $row->meta : [];
                if (! empty($meta['interaction'])) {
                    continue;
                }
                $keep = array_intersect_key($meta, array_flip(['last_score', 'last_seconds', 'last_answer', 'last_difficulty']));
                $row->meta = array_merge(PracticePayload::build($row), $keep);
                $row->save();
            }
        });
    }

    private function seedTypedShowcase(): void
    {
        $tasks = TaskType::query()->where('skill', '!=', 'mock')->get()->keyBy('slug');

        foreach ($this->typedShowcase() as $row) {
            $task = $tasks[$row['task_slug']] ?? null;
            if (! $task) {
                continue;
            }
            $item = PracticeQuestion::query()->updateOrCreate(
                ['external_id' => $row['external_id']],
                [
                    'task_type_id' => $task->id,
                    'skill' => $task->skill,
                    'task_slug' => $task->slug,
                    'title' => $row['title'],
                    'difficulty' => $row['difficulty'] ?? 'Medium',
                    'appeared' => $row['appeared'] ?? 24,
                    'is_prediction' => true,
                    'prompt' => $row['prompt'] ?? '',
                    'passage' => $row['passage'] ?? '',
                    'image_url' => $row['image_url'] ?? null,
                    'exam_slugs' => $task->exam_slugs ?? ['pte'],
                ]
            );
            $built = PracticePayload::build($item);
            $meta = array_merge($built, $row['meta'] ?? []);
            $meta['interaction'] = PracticeKind::mode($task->slug);
            $item->meta = $meta;
            $item->save();
        }
    }

    private function typedShowcase(): array
    {
        $library = 'Libraries have always been more than buildings that store books. They are quiet laboratories where communities learn, debate, and reinvent themselves. Today, many libraries invite residents to borrow tools, attend coding workshops, and meet visiting experts who share practical skills. By blending print collections with digital services, these spaces keep knowledge accessible for everyone.';

        return [
            [
                'external_id' => 'nvs-ra-01',
                'task_slug' => 'read-aloud',
                'title' => 'Quiet laboratories',
                'passage' => $library,
                'prompt' => $library,
            ],
            [
                'external_id' => 'nvs-rs-01',
                'task_slug' => 'repeat-sentence',
                'title' => 'Measurement turns ideas into budgets',
                'passage' => 'Clear measurement is what turns a good idea into a budget line.',
                'meta' => ['audio_text' => 'Clear measurement is what turns a good idea into a budget line.'],
            ],
            [
                'external_id' => 'nvs-di-01',
                'task_slug' => 'describe-image',
                'title' => 'Weekly screen time by age',
                'passage' => 'The chart compares average weekly screen time across four age groups.',
                'image_url' => '/uploads/di-screen-time.svg',
                'prompt' => 'Describe the chart. Mention the highest group, a comparison, and the overall trend.',
            ],
            [
                'external_id' => 'nvs-essay-01',
                'task_slug' => 'essay',
                'title' => 'Car-free city centres',
                'difficulty' => 'Difficult',
                'prompt' => 'Some cities restrict private cars in the centre. Do the advantages outweigh the disadvantages? Write 200–300 words.',
                'passage' => 'Several European cities now limit private cars in historic centres. Supporters point to cleaner air and safer streets. Critics argue that shops lose customers who used to drive in from the suburbs, and that night-shift workers need late buses that do not yet exist.',
                'meta' => [
                    'word_limit' => ['min' => 200, 'max' => 300],
                    'sample_answer' => 'Restricting cars in city centres can improve air quality and safety, but cities must fund reliable night transit or suburban shoppers and shift workers are left behind.',
                ],
            ],
            [
                'external_id' => 'nvs-swt-01',
                'task_slug' => 'swt',
                'title' => 'Libraries as laboratories',
                'prompt' => 'Summarize the passage in one sentence (5–75 words).',
                'passage' => $library,
                'meta' => [
                    'word_limit' => ['min' => 5, 'max' => 75],
                    'sample_answer' => 'Modern libraries mix books with tools, workshops, and digital services so communities can keep learning in one accessible place.',
                ],
            ],
            [
                'external_id' => 'nvs-task1-01',
                'task_slug' => 'task1',
                'title' => 'Screen time report',
                'prompt' => 'Summarise the information by selecting and reporting the main features, and make comparisons where relevant.',
                'passage' => 'Average weekly screen time: ages 8–12 = 14 hours; 13–17 = 22 hours; 18–24 = 28 hours; 25–34 = 19 hours. The 18–24 group is highest. The youngest group is lowest. Time rises through adolescence, then falls after 24.',
                'image_url' => '/uploads/di-screen-time.svg',
                'meta' => ['word_limit' => ['min' => 150, 'max' => 180]],
            ],
            [
                'external_id' => 'nvs-case-01',
                'task_slug' => 'case-write',
                'title' => 'Campus bike-share deficit',
                'prompt' => 'The student union bike-share is losing 18% of bikes each term. Recommend one action and name one risk.',
                'passage' => 'A campus bike-share launched with 400 bikes. After three terms, 18% are missing or damaged. Users say locks fail after 8pm. Security will not staff the docks at night. A vendor offered GPS locks at twice the current maintenance budget.',
                'meta' => ['word_limit' => ['min' => 120, 'max' => 250]],
            ],
            [
                'external_id' => 'nvs-rc-01',
                'task_slug' => 'rc-mcq',
                'title' => 'What libraries are for',
                'passage' => $library,
                'prompt' => 'What is the main claim of this passage?',
                'meta' => [
                    'options' => [
                        'Libraries are quiet laboratories where communities keep learning with books and digital services.',
                        'Libraries should stop lending books and only run coding workshops.',
                        'Visiting experts have replaced librarians in most cities.',
                        'Print collections are no longer useful once wifi is installed.',
                    ],
                    'answer_key' => ['kind' => 'single', 'index' => 0],
                    'correct_text' => 'Libraries are quiet laboratories where communities keep learning with books and digital services.',
                ],
            ],
            [
                'external_id' => 'nvs-rwfib-01',
                'task_slug' => 'rw-fib',
                'title' => 'Accessible knowledge',
                'passage' => $library,
            ],
            [
                'external_id' => 'nvs-rfib-01',
                'task_slug' => 'r-fib',
                'title' => 'Blended collections',
                'passage' => $library,
            ],
            [
                'external_id' => 'nvs-reorder-01',
                'task_slug' => 'reorder',
                'title' => 'How libraries changed',
                'passage' => $library,
            ],
            [
                'external_id' => 'nvs-lmcq-01',
                'task_slug' => 'l-mcq',
                'title' => 'Lecture: public libraries',
                'passage' => $library,
                'prompt' => 'According to the talk, which statement is most accurate?',
                'meta' => [
                    'audio_text' => $library,
                    'options' => [
                        'Libraries now mix print collections with digital services and workshops.',
                        'Libraries have closed their print collections.',
                        'Only coding workshops are allowed in modern libraries.',
                        'Experts no longer visit public libraries.',
                    ],
                    'answer_key' => ['kind' => 'single', 'index' => 0],
                ],
            ],
            [
                'external_id' => 'nvs-wfd-01',
                'task_slug' => 'wfd',
                'title' => 'Dictation: measurement',
                'passage' => 'Clear measurement is what turns a good idea into a budget line.',
                'meta' => [
                    'audio_text' => 'Clear measurement is what turns a good idea into a budget line.',
                    'correct_text' => 'clear measurement is what turns a good idea into a budget line',
                    'answer_key' => ['kind' => 'text', 'value' => 'clear measurement is what turns a good idea into a budget line'],
                ],
            ],
            [
                'external_id' => 'nvs-sst-01',
                'task_slug' => 'sst',
                'title' => 'Summarize: libraries',
                'passage' => $library,
                'meta' => [
                    'audio_text' => $library,
                    'word_limit' => ['min' => 50, 'max' => 70],
                    'sample_answer' => 'The speaker says libraries are still places to learn, and now also lend tools, run workshops, and keep knowledge accessible with digital services.',
                ],
            ],
            [
                'external_id' => 'nvs-hiw-01',
                'task_slug' => 'hiw',
                'title' => 'Highlight: library transcript',
                'passage' => $library,
            ],
            [
                'external_id' => 'nvs-asq-01',
                'task_slug' => 'answer-short',
                'title' => 'What keeps knowledge accessible?',
                'passage' => $library,
                'meta' => [
                    'audio_text' => 'What keeps knowledge accessible in modern libraries?',
                    'correct_text' => 'print and digital services',
                    'answer_key' => ['kind' => 'text', 'value' => 'print and digital services'],
                ],
            ],
            [
                'external_id' => 'nvs-cue-01',
                'task_slug' => 'cue-card',
                'title' => 'A public place that helps you study',
                'prompt' => 'Describe a public place that helps you study. You should say: where it is, how often you go, what you do there, and explain why it helps you.',
                'passage' => 'Talk about a library, park, or cafe. Cover place, frequency, activity, and why it works for you.',
            ],
            [
                'external_id' => 'nvs-int-01',
                'task_slug' => 'interview',
                'title' => 'Do you prefer studying alone?',
                'prompt' => 'Do you prefer studying alone or with other people? Why?',
                'passage' => 'Give a clear preference, one reason, and one short example.',
            ],
        ];
    }

    private function seedExtraMocks(): void
    {
        foreach ($this->extraMockRows() as $row) {
            MockTest::query()->updateOrCreate(['slug' => $row['slug']], $row);
        }
    }

    private function seedMockBanks(): void
    {
        foreach (MockTest::query()->orderBy('id')->get() as $mock) {
            if ($mock->runner === 'pte') {
                continue;
            }
            if ($mock->examQuestions()->exists()) {
                continue;
            }

            $paper = $this->readingPapers()[$mock->slug] ?? null;
            if ($paper) {
                $this->attachReadingPaper($mock, $paper);
                continue;
            }

            $count = match ($mock->section) {
                'reading' => 13,
                'listening' => 20,
                'writing' => 8,
                'speaking' => 12,
                default => 20,
            };

            $this->attachMcqBank($mock, $count);
        }
    }

    private function seedExtraPte(): void
    {
        $sort = PteBank::EXTRA_SORT_START;

        foreach ($this->extraPteItems() as $item) {
            PteQuestion::query()->updateOrCreate(
                ['external_id' => $item['external_id']],
                [
                    'type' => $item['type'],
                    'section' => $item['section'],
                    'title' => $item['title'],
                    'instructions' => $item['instructions'],
                    'payload' => $item['payload'],
                    'image_url' => $item['image_url'] ?? null,
                    'correct_text' => $item['correct_text'] ?? null,
                    'answer_key' => $item['answer_key'] ?? null,
                    'sort_order' => $sort++,
                ]
            );
        }
    }

    private function refreshCounts(): void
    {
        foreach (TaskType::query()->where('skill', '!=', 'mock')->get() as $task) {
            $count = PracticeQuestion::query()->where('task_slug', $task->slug)->count();
            if ($count > 0) {
                $practiced = PracticeQuestion::query()
                    ->where('task_slug', $task->slug)
                    ->where('practiced', true)
                    ->count();
                $task->update([
                    'total' => $count,
                    'done' => $practiced,
                ]);
            }
        }

        $full = MockTest::query()->where('section', 'full')->count();
        $sectional = MockTest::query()->where('section', '!=', 'full')->count();
        TaskType::query()->where('slug', 'full-mock')->update(['total' => $full]);
        TaskType::query()->where('slug', 'sectional-mock')->update(['total' => $sectional]);

        foreach (MockTest::query()->get() as $mock) {
            if ($mock->runner === 'pte') {
                $mock->update(['questions' => PteBank::forMock($mock->slug)->count()]);
                continue;
            }
            $count = $mock->examQuestions()->count();
            if ($count > 0) {
                $mock->update(['questions' => $count]);
            }
        }
    }

    private function promptFor(TaskType $task, string $title, string $passage): string
    {
        return match ($task->slug) {
            'essay', 'task1', 'case-write' => "Write a complete response on “{$title}”. Use the passage as source material.\n\n{$passage}",
            'swt' => "Summarize the following text in one sentence.\n\n{$passage}",
            'answer-short' => "Answer in a few words: What is the main idea of “{$title}”?",
            'cue-card', 'interview' => "Speak for up to two minutes about “{$title}”. Cover what it is, why it matters, and one example from your experience.",
            'describe-image' => "Describe the chart or diagram linked to “{$title}”. Mention the highest value, a comparison, and the overall trend.",
            'retell-lecture' => "You will hear a short lecture on “{$title}”. Retell the key points in your own words.\n\n{$passage}",
            'wfd', 'hiw', 'l-mcq', 'sst' => "Listen carefully. Transcript for practice:\n\n{$passage}",
            default => $passage,
        };
    }

    private function attachReadingPaper(MockTest $mock, array $paper): void
    {
        foreach ($paper['passages'] as $i => $row) {
            MockPassage::query()->create([
                'mock_test_id' => $mock->id,
                'label' => $row['label'],
                'text' => $row['text'],
                'sort_order' => $i,
            ]);
        }

        foreach ($paper['questions'] as $row) {
            MockQuestion::query()->create([
                'mock_test_id' => $mock->id,
                'number' => $row['number'],
                'part' => $row['part'],
                'prompt' => $row['prompt'],
                'options' => $row['options'],
                'correct_index' => $row['correct_index'],
                'skill_tag' => $row['skill_tag'],
                'tip' => $row['tip'],
            ]);
        }
    }

    private function attachMcqBank(MockTest $mock, int $count): void
    {
        $items = $this->mcqPool();
        $offset = abs(crc32($mock->slug)) % max(1, count($items));
        $passages = $this->passages();

        if (in_array($mock->section, ['reading', 'full', 'listening'], true)) {
            foreach (['A', 'B', 'C'] as $i => $label) {
                MockPassage::query()->create([
                    'mock_test_id' => $mock->id,
                    'label' => $label,
                    'text' => $passages[($offset + $i) % count($passages)],
                    'sort_order' => $i,
                ]);
            }
        }

        for ($n = 0; $n < $count; $n++) {
            $item = $items[($offset + $n) % count($items)];
            $part = $n < (int) ($count / 3) ? 1 : ($n < (int) (2 * $count / 3) ? 2 : 3);
            MockQuestion::query()->create([
                'mock_test_id' => $mock->id,
                'number' => $n + 1,
                'part' => $mock->section === 'full' ? $part : 1,
                'prompt' => $item[0],
                'options' => $item[1],
                'correct_index' => $item[2],
                'skill_tag' => $item[3],
                'tip' => $item[4],
            ]);
        }
    }

    private function extraMockRows(): array
    {
        return [
            ['slug' => 'pte-full-c', 'exam_slug' => 'pte', 'section' => 'full', 'title' => 'PTE Full Mock C', 'subtitle' => 'Second academic sequence · extra bank', 'duration_min' => 120, 'questions' => 40, 'attempts' => 0, 'difficulty' => 'Advanced', 'featured' => true, 'runner' => 'pte'],
            ['slug' => 'pte-s-plus', 'exam_slug' => 'pte', 'section' => 'speaking', 'title' => 'Speaking Extra Pack', 'subtitle' => 'RA · RS · DI · RL · ASQ extra items', 'duration_min' => 30, 'questions' => 20, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'pte'],
            ['slug' => 'pte-w-plus', 'exam_slug' => 'pte', 'section' => 'writing', 'title' => 'Writing Extra Pack', 'subtitle' => 'More SWT and essay prompts', 'duration_min' => 40, 'questions' => 8, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'pte'],
            ['slug' => 'pte-r-plus', 'exam_slug' => 'pte', 'section' => 'reading', 'title' => 'Reading Extra Pack', 'subtitle' => 'RWFIB · MCQ · Reorder extra set', 'duration_min' => 30, 'questions' => 16, 'attempts' => 0, 'difficulty' => 'Advanced', 'featured' => false, 'runner' => 'pte'],
            ['slug' => 'pte-l-plus', 'exam_slug' => 'pte', 'section' => 'listening', 'title' => 'Listening Extra Pack', 'subtitle' => 'SST · HIW · WFD extra set', 'duration_min' => 25, 'questions' => 16, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'pte'],
            ['slug' => 'ielts-r-night-shift', 'exam_slug' => 'ielts', 'section' => 'reading', 'title' => 'The Biology of Night Work', 'subtitle' => 'Sectional · matching + MCQ', 'duration_min' => 20, 'questions' => 13, 'attempts' => 0, 'difficulty' => 'Advanced', 'featured' => true, 'runner' => 'ielts-reading'],
            ['slug' => 'ielts-r-coral-reefs', 'exam_slug' => 'ielts', 'section' => 'reading', 'title' => 'Rebuilding Coral Reefs', 'subtitle' => 'Academic Reading · 3 paragraphs', 'duration_min' => 20, 'questions' => 13, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'ielts-reading'],
            ['slug' => 'ielts-l-cam11-t1', 'exam_slug' => 'ielts', 'section' => 'listening', 'title' => 'Cambridge 11 — Test 1', 'subtitle' => 'Campus tour + lecture mix', 'duration_min' => 30, 'questions' => 20, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'generic'],
            ['slug' => 'ielts-s-cue-pack3', 'exam_slug' => 'ielts', 'section' => 'speaking', 'title' => 'Speaking Pack — Cue Cards 03', 'subtitle' => 'Work, study, and hometown', 'duration_min' => 15, 'questions' => 12, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'generic'],
            ['slug' => 'ielts-full-02', 'exam_slug' => 'ielts', 'section' => 'full', 'title' => 'IELTS Full Mock — Session 02', 'subtitle' => 'Timed mixed-skill paper', 'duration_min' => 165, 'questions' => 20, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'generic'],
            ['slug' => 'bba-full-03', 'exam_slug' => 'bba', 'section' => 'full', 'title' => 'BBA Entrance Full Mock 03', 'subtitle' => 'Quant · Verbal · Logic extra paper', 'duration_min' => 90, 'questions' => 20, 'attempts' => 0, 'difficulty' => 'Advanced', 'featured' => false, 'runner' => 'generic'],
            ['slug' => 'bba-q-quant', 'exam_slug' => 'bba', 'section' => 'reading', 'title' => 'BBA Quant Drill', 'subtitle' => 'Arithmetic and data interpretation', 'duration_min' => 25, 'questions' => 20, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'generic'],
            ['slug' => 'mba-full-02', 'exam_slug' => 'mba', 'section' => 'full', 'title' => 'MBA Entrance Full Mock 02', 'subtitle' => 'CR · RC · Quant extra paper', 'duration_min' => 120, 'questions' => 20, 'attempts' => 0, 'difficulty' => 'Advanced', 'featured' => true, 'runner' => 'generic'],
            ['slug' => 'mba-r-rc-pack', 'exam_slug' => 'mba', 'section' => 'reading', 'title' => 'MBA RC Extra Pack', 'subtitle' => 'Short passages with inference items', 'duration_min' => 30, 'questions' => 13, 'attempts' => 0, 'difficulty' => 'Standard', 'featured' => false, 'runner' => 'generic'],
            ['slug' => 'eng-full-02', 'exam_slug' => 'english-basic', 'section' => 'full', 'title' => 'Placement Full Check 02', 'subtitle' => 'Second mixed-skills placement', 'duration_min' => 45, 'questions' => 20, 'attempts' => 0, 'difficulty' => 'Foundation', 'featured' => false, 'runner' => 'generic'],
            ['slug' => 'eng-r-b1', 'exam_slug' => 'english-basic', 'section' => 'reading', 'title' => 'Everyday Reading B1', 'subtitle' => 'Notices, emails, and short articles', 'duration_min' => 20, 'questions' => 13, 'attempts' => 0, 'difficulty' => 'Foundation', 'featured' => false, 'runner' => 'generic'],
        ];
    }

    private function titles(): array
    {
        return [
            'Coral Nurseries', 'Night Shift Biology', 'Urban Wetlands', 'Heat Island Effect',
            'Microplastic Filters', 'Peer Tutoring', 'Cash Flow Habits', 'Electric Buses',
            'Community Gardens', 'Open Source Labs', 'Sleep Pressure', 'Street Sensors',
            'Hybrid Offices', 'Wetland Carbon', 'Museum Lighting', 'Bee Navigation',
            'River Restoration', 'Public Transit Maps', 'Campus Libraries', 'Food Miles',
            'Reflective Roofs', 'Freshwater Fish', 'Invoice Discipline', 'Textile Shedding',
            'Green Corridors', 'Study Portals', 'Lecture Notes', 'Harbor Seagrass',
            'Quiet Hours', 'Tool Lending', 'Coding Clubs', 'Visitor Experts',
            'Rain Gardens', 'Night Buses', 'Shared Kitchens', 'Local Archives',
            'Farmers Markets', 'Bike Lanes', 'River Walks', 'Roof Gardens',
            'Science Fairs', 'Language Cafes', 'Maker Spaces', 'Youth Mentors',
            'Coastal Dunes', 'Alpine Forests', 'Desert Irrigation', 'City Compost',
            'Harbor Logistics', 'School Meal Programs', 'Vaccine Cold Chains', 'River Deltas',
            'Heatwave Shelters', 'Bicycle Highways', 'Museum Conservation', 'Farm Soil Carbon',
            'Coastal Wind Farms', 'Public Health Clinics', 'Waste Sorting Hubs', 'Elder Care Networks',
            'Student Housing Co-ops', 'Drought Forecasts', 'Urban Beekeeping', 'Ferry Timetables',
            'Library Outreach Vans', 'Plastic-Free Aisles', 'Apprenticeship Paths', 'Wetland Birds',
            'Rooftop Solar Leases', 'Community Clinics', 'Flood Siren Tests', 'Shared Childcare',
            'Tram Signal Priority', 'Kitchen Incubators', 'Reef Tourism Caps', 'Night Study Halls',
            'Water Meter Audits', 'Textile Repair Shops', 'Campus Food Banks', 'Shade Tree Bonds',
            'Harbor Oil Booms', 'Rural Broadband', 'City Compost Routes', 'Volunteer Rangers',
            'Low-Tide Walkways', 'School Garden Plots', 'Quiet Carriage Rules', 'Emergency Cooling Centers',
        ];
    }

    private function lenses(): array
    {
        return [
            'policy brief', 'field study', 'student seminar', 'city council note',
            'lab summary', 'community report', 'exam briefing', 'research abstract',
            'training memo', 'case review', 'news digest', 'workshop outline',
            'site visit', 'progress audit', 'safety circular', 'planning draft',
            'public FAQ', 'methods note', 'impact review', 'tutorial prompt',
        ];
    }

    private function facts(): array
    {
        return [
            'small, regular actions outperform one-off campaigns',
            'local staff who stay for several seasons keep quality higher',
            'clear measurement is what turns a good idea into a budget line',
            'the first dry year is when most projects quietly fail',
            'shared equipment only works if someone repairs what breaks',
            'residents support projects they can walk to in ten minutes',
            'data without a weekday owner is rarely used again',
            'cheap pilots still need a plan for the second year',
            'night hours matter more than extra weekend events for workers',
            'shade, water, and rest are treated as infrastructure, not extras',
            'short explanations beat long lectures when people are tired',
            'one accurate forecast prevents a week of emergency borrowing',
            'marked routes let ports and habitats operate at the same time',
            'students without a quiet desk at home rely on public hours',
            'volunteers stay when the land tenure is secure',
            'absolute claims usually fail when the evidence is local and mixed',
            'cooling a street is often cheaper than cooling every apartment',
            'a protected sleep window cuts errors more than extra caffeine',
            'restored habitat can return fish within a decade if water stays clean',
            'invoices that sit unpaid can stop a healthy product from covering wages',
        ];
    }

    private function titleFor(int $taskIndex, int $seq, array $titles): string
    {
        $base = $titles[($taskIndex * 19 + $seq * 7) % count($titles)];
        $lens = $this->lenses()[($taskIndex * 5 + $seq * 2) % count($this->lenses())];

        return $base.': '.$lens.' '.($seq + 1);
    }

    private function passageFor(int $taskIndex, int $seq, array $passages): string
    {
        $stock = $passages[($taskIndex * 3 + $seq) % count($passages)];
        $topic = strtolower($this->titles()[($taskIndex * 19 + $seq * 7) % count($this->titles())]);
        $lens = $this->lenses()[($taskIndex * 5 + $seq * 2) % count($this->lenses())];
        $fact = $this->facts()[($taskIndex + $seq * 7) % count($this->facts())];

        $frames = [
            "This {$lens} on {$topic} starts from a practical question: what still works after the first season. {$stock} A useful takeaway is that {$fact}.",
            "Consider {$topic} through a {$lens}. {$stock} Follow-up work should test whether {$fact}.",
            "A short briefing on {$topic} ({$lens}) is included below. {$stock} Examiners look for the central claim: {$fact}.",
            "Read the notes on {$topic}. {$stock} The {$lens} stresses that {$fact}.",
        ];

        return $frames[$seq % count($frames)];
    }

    private function passages(): array
    {
        return [
            'Libraries have always been more than buildings that store books. They are quiet laboratories where communities learn, debate, and reinvent themselves. Today, many libraries invite residents to borrow tools, attend coding workshops, and meet visiting experts who share practical skills. By blending print collections with digital services, these spaces keep knowledge accessible for everyone.',
            'Urban wetlands filter stormwater, store carbon, and give migrating birds a place to rest. Cities that restore marshes along river edges often see fewer floods and cooler summer streets. The challenge is balancing housing demand with habitat protection, which requires planners to treat green space as essential infrastructure rather than leftover land.',
            'Night-shift workers fight against circadian rhythms that expect darkness for repair and memory. Hospitals and factories that add brighter daytime lighting, darker rest rooms, and predictable rotas report fewer errors. Researchers caution that extra caffeine cannot replace a protected sleep window after the shift ends.',
            'Coral nurseries grow fragments on underwater frames until they are strong enough to attach to damaged reefs. When local communities help maintain the sites, survival rates rise because fishing pressure and boat anchors are reduced. Restoration is slow, but it can rebuild fish habitat within a decade if water quality stays stable.',
            'Electric buses cut tailpipe pollution on busy corridors, yet their climate benefit depends on how electricity is generated. A grid powered by coal offers smaller gains than one dominated by wind and solar. Cities therefore pair fleet upgrades with cleaner power purchase agreements.',
            'Peer tutoring helps both partners: tutors rehearse explanations, and tutees ask questions they might avoid in a lecture hall. Programs work best when sessions are short, regular, and focused on one concept at a time. Staff still need to check that explanations stay accurate.',
            'Small businesses often fail from weak cash-flow planning rather than unpopular products. Owners chase new sales while invoices sit unpaid for weeks. Simple forecasts and clearer payment terms prevent sudden shortfalls even when demand looks healthy.',
            'Microplastics enter rivers from clothing fibers, packaging, and tire wear. Once in the water they absorb chemicals and move up the food chain. Better wastewater filters and cooler, shorter wash cycles can reduce the load reaching lakes and seas.',
            'Community gardens strengthen neighborhood ties, supply fresh produce, and give children an outdoor classroom. They also cut food miles. The gardens remain viable only when volunteers and land tenure are secure from season to season.',
            'Open-source software grows because developers worldwide can inspect code, fix bugs, and propose features. Companies adopt it to avoid vendor lock-in and to customize tools. The trade-off is that documentation can be uneven and skilled maintainers are still required.',
            'Sleep pressure builds the longer a person stays awake, while circadian rhythms tell the body when rest should arrive. During deep sleep the brain clears metabolic waste more efficiently. Consistent bedtimes therefore support memory, mood, and next-day focus.',
            'Researchers install air-quality sensors on streetlights to map pollution through the day. After several months, peaks appear near major intersections. City teams then use the data to time traffic restrictions and plant green corridors where exposure is highest.',
            'Hybrid offices keep some collaboration days in person while protecting quiet focus time at home. Teams that write decisions down reduce the cost of missing a meeting. Mentoring junior staff still needs scheduled face-to-face hours, not only chat threads.',
            'Reflective roofs and street trees lower surface temperatures in dense districts. Permeable pavements let rain soak away instead of pooling as heat. These changes are cheaper than rebuilding entire neighborhoods and they improve comfort during heat waves.',
            'Campus libraries now lend laptops, run late-night study rooms, and host language cafes. Students who lack a quiet desk at home rely on these hours. Staff argue that longer opening times are an equity measure, not a luxury.',
            'Seagrass meadows stabilize harbor mud and store carbon in their roots. Dredging and boat propellers tear the beds, releasing sediment that clouds the water. Slow-speed zones and marked channels help the plants recover without closing the port.',
        ];
    }

    private function readingPapers(): array
    {
        return [
            'ielts-r-cam12-t1' => $this->paperUrbanWetlands(),
            'ielts-r-night-shift' => $this->paperNightShift(),
            'ielts-r-coral-reefs' => $this->paperCoral(),
            'mba-r-rc-pack' => $this->paperUrbanWetlands(),
        ];
    }

    private function paperUrbanWetlands(): array
    {
        return [
            'passages' => [
                ['label' => 'A', 'text' => 'For most of the twentieth century, city engineers treated marshes as wasted ground. Rivers were straightened, banks were paved, and floodplains were filled so roads and warehouses could sit closer to the water. The short-term gain was extra land. The long-term cost appeared whenever heavy rain arrived: water that once spread slowly across reeds now surged into streets and basements.'],
                ['label' => 'B', 'text' => 'Urban wetland restoration tries to reverse that bargain. Parks along river edges are designed to flood on purpose, holding stormwater for hours while plants trap sediment. Birds use the same strips as rest stops on migration routes. Because the parks also shade paths and cool nearby apartments, residents support them for recreation even if they never use the word “infrastructure”.'],
                ['label' => 'C', 'text' => 'The hardest negotiations concern housing. Dense cities need homes, and restored marsh can look, on a map, like unused plots. Successful projects therefore stack uses: raised walkways, weekend markets, and floodable sports fields sit above the wet soil. Planners argue that green space is not leftover land. It is the system that keeps the rest of the city usable after a storm.'],
            ],
            'questions' => [
                $this->q(1, 1, 'Paragraph A mainly explains that paving riverbanks:', ['Created land but increased flood damage later.', 'Ended all warehouse construction near rivers.', 'Made rainwater soak away more slowly into reeds.', 'Removed the need for city engineers.'], 0, 'Main idea', 'Look for short-term gain versus long-term cost.'),
                $this->q(2, 1, 'According to paragraph A, floodplains were filled so that:', ['Birds could rest during migration.', 'Roads and warehouses could sit closer to water.', 'Parks could flood on purpose.', 'Housing towers could include weekend markets.'], 1, 'Detail', 'Match the purpose stated in the same sentence.'),
                $this->q(3, 1, 'The phrase “the long-term cost” refers to:', ['Lost recreation space for apartment residents.', 'Higher warehouse rents beside the river.', 'Floodwater rushing into streets and basements.', 'The expense of planting extra reeds.'], 2, 'Vocabulary in context', 'Read one line after the phrase.'),
                $this->q(4, 2, 'Urban wetland parks are designed to:', ['Prevent any water from entering the city.', 'Hold stormwater and trap sediment.', 'Replace all apartment cooling systems.', 'Straighten rivers for faster shipping.'], 1, 'Detail', 'Cause → effect language in paragraph B.'),
                $this->q(5, 2, 'Residents support restored parks mainly because they:', ['Offer shade, cooling, and recreation.', 'Guarantee cheaper housing nearby.', 'Remove the need for storm drains.', 'Are labeled as infrastructure on maps.'], 0, 'Inference', 'The last sentence of B explains everyday reasons.'),
                $this->q(6, 2, 'Which statement is NOT mentioned in paragraph B?', ['Birds use the strips as rest stops.', 'Parks can flood on purpose.', 'Plants trap sediment.', 'The wetlands produce drinking water for sale.'], 3, 'Not given', 'Eliminate options that appear in the paragraph.'),
                $this->q(7, 2, 'The word “strips” in paragraph B refers to:', ['Warehouse roofs.', 'River-edge parks.', 'Migration maps.', 'Apartment stairwells.'], 1, 'Reference', 'The noun sits near “the same”.'),
                $this->q(8, 3, 'The hardest negotiations concern:', ['Bird migration calendars.', 'Warehouse design.', 'Housing demand versus marsh land.', 'Weekend market prices.'], 2, 'Main idea', 'Opening of paragraph C.'),
                $this->q(9, 3, 'Successful projects “stack uses” by:', ['Building only single-purpose flood walls.', 'Putting walkways, markets, and fields above wet soil.', 'Filling marshes so homes can sit on dry concrete.', 'Moving all parks outside the city.'], 1, 'Detail', 'Look for the colon that lists examples.'),
                $this->q(10, 3, 'Planners argue that green space is:', ['Leftover land after housing is finished.', 'Useful only for weekend markets.', 'Essential infrastructure after storms.', 'Too expensive compared with paved banks.'], 2, 'Writer’s view', 'Final two sentences of C.'),
                $this->q(11, 3, 'Raised walkways are mentioned as a way to:', ['Keep public access while the soil floods.', 'Replace floodable sports fields.', 'Straighten the river channel.', 'Increase warehouse storage.'], 0, 'Inference', 'Stacked uses keep people above wet ground.'),
                $this->q(12, 3, 'A suitable title for the passage is:', ['Why warehouses should return to floodplains', 'Restoring city wetlands as working infrastructure', 'How birds design urban housing policy', 'Ending all riverside recreation'], 1, 'Global', 'Combine the three paragraph roles.'),
                $this->q(13, 3, 'The writer’s overall position is that wetlands should be:', ['Removed so more homes can be built at grade.', 'Kept only in rural areas.', 'Designed as multi-use flood protection in cities.', 'Replaced with deeper concrete channels.'], 2, 'Writer’s view', 'Do not pick an extreme opposite of the argument.'),
            ],
        ];
    }

    private function paperNightShift(): array
    {
        return [
            'passages' => [
                ['label' => 'A', 'text' => 'Human biology expects darkness for repair. Body temperature drops, melatonin rises, and the brain clears metabolic waste most efficiently during a night-time sleep window. Night-shift work fights that clock. Nurses, drivers, and factory teams stay alert while their cells still behave as if it were time to rest, which is why error rates often peak in the hours before dawn.'],
                ['label' => 'B', 'text' => 'Hospitals that redesigned lighting and rotas saw measurable gains. Brighter, cooler light during the working night helps workers stay awake; fully dark rest rooms let them nap between tasks. Rotas that avoid rapid switches from day to night reduce the jet-lag effect. Extra caffeine can mask tiredness for an hour, but it cannot replace a protected sleep window after the shift ends.'],
                ['label' => 'C', 'text' => 'Critics say 24-hour services cannot pause for circadian science. Researchers reply that the question is not whether nights are staffed, but how. Predictable schedules, quieter break rooms, and limits on consecutive nights cut mistakes without reducing coverage. The evidence suggests that treating sleep as a safety system is cheaper than treating the accidents that follow chronic exhaustion.'],
            ],
            'questions' => [
                $this->q(1, 1, 'Paragraph A states that night-shift work is difficult because:', ['Workers prefer brighter hospital lights.', 'It conflicts with the body’s night-time repair clock.', 'Factories refuse to hire drivers.', 'Melatonin rises during the day only.'], 1, 'Main idea', 'Match biology versus schedule.'),
                $this->q(2, 1, 'Error rates often peak:', ['Just after sunset.', 'At midday meetings.', 'In the hours before dawn.', 'During fully dark naps.'], 2, 'Detail', 'Last sentence of A.'),
                $this->q(3, 1, 'During night-time sleep, the brain:', ['Produces more workplace errors.', 'Clears metabolic waste more efficiently.', 'Stops melatonin production.', 'Raises body temperature sharply.'], 1, 'Detail', 'Look for “most efficiently”.'),
                $this->q(4, 2, 'Brighter, cooler light is used to:', ['Help workers stay awake on the night shift.', 'Replace dark rest rooms.', 'Shorten the sleep window after work.', 'Increase caffeine sales.'], 0, 'Detail', 'Cause and purpose in B.'),
                $this->q(5, 2, 'Rapid switches from day to night are compared to:', ['A lighting upgrade.', 'A jet-lag effect.', 'A protected sleep window.', 'A quieter break room.'], 1, 'Vocabulary in context', 'The comparison sits in the same sentence.'),
                $this->q(6, 2, 'The writer says extra caffeine:', ['Fully replaces sleep after a night shift.', 'Has no effect on tiredness.', 'Can mask tiredness only briefly.', 'Should be banned in hospitals.'], 2, 'Writer’s view', 'Contrast “mask” with “cannot replace”.'),
                $this->q(7, 2, 'Fully dark rest rooms are intended to:', ['Keep workers awake until dawn.', 'Let staff nap between tasks.', 'Increase consecutive night shifts.', 'Reduce coverage on the ward.'], 1, 'Detail', 'Purpose clause in B.'),
                $this->q(8, 3, 'Critics argue that:', ['Sleep science cannot stop 24-hour services.', 'Caffeine should replace rotas.', 'Night work has no error risk.', 'Break rooms should be removed.'], 0, 'Main idea', 'Opening of C.'),
                $this->q(9, 3, 'Researchers reply that the real question is:', ['Whether nights are staffed at all.', 'How night work is organized.', 'Whether factories should close.', 'How to increase consecutive nights.'], 1, 'Detail', '“not whether… but how”.'),
                $this->q(10, 3, 'Which measure is mentioned as cutting mistakes?', ['Removing all night coverage.', 'Unpredictable last-minute rotas.', 'Limits on consecutive nights.', 'Brighter break rooms at all hours.'], 2, 'Detail', 'List in paragraph C.'),
                $this->q(11, 3, 'Treating sleep as a safety system is described as:', ['More expensive than accident care.', 'Cheaper than treating later accidents.', 'Irrelevant to factory teams.', 'Useful only for drivers.'], 1, 'Writer’s view', 'Final sentence.'),
                $this->q(12, 3, 'A good heading for paragraph C is:', ['Why night work should end', 'Staffing nights more safely', 'The chemistry of melatonin', 'Caffeine as a complete solution'], 1, 'Heading', 'C accepts night work and improves it.'),
                $this->q(13, 3, 'The passage as a whole argues that:', ['Biology makes all night work impossible.', 'Better schedules and rest spaces reduce night-shift harm.', 'Lighting changes have no measured effect.', 'Coverage must fall if sleep is protected.'], 1, 'Global', 'Combine B’s evidence with C’s reply.'),
            ],
        ];
    }

    private function paperCoral(): array
    {
        return [
            'passages' => [
                ['label' => 'A', 'text' => 'Coral reefs are living walls. Tiny animals build limestone skeletons that shelter fish, slow waves before they hit the shore, and support coastal tourism. When water stays too warm, the animals expel the algae that feed them, and the reef turns white. A short bleaching event can be survived. Repeated heat, dirty water, and damaging fishing leave a rubble field that no longer protects the coast.'],
                ['label' => 'B', 'text' => 'Coral nurseries try to restart growth. Divers clip healthy fragments, hang them on underwater frames, and wait until the pieces are sturdy enough to cement onto damaged rock. Survival is higher when nearby villages help: fewer boat anchors drag across the site, and fishing pressure drops around the nursery. The work is slow. A decade may pass before a restored patch again looks like a forest of branching coral.'],
                ['label' => 'C', 'text' => 'Scientists warn that nurseries cannot outrun climate change alone. If seas keep warming, even carefully tended fragments will bleach. Restoration therefore sits beside other policies: cleaner shoreline drains, limits on destructive gear, and cuts in greenhouse gases. Communities still value the local projects because they rebuild fish habitat now, train divers, and keep attention on a reef that might otherwise be written off as lost.'],
            ],
            'questions' => [
                $this->q(1, 1, 'Paragraph A compares coral reefs to:', ['Rubble fields.', 'Living walls.', 'Boat anchors.', 'Shoreline drains.'], 1, 'Main idea', 'Opening metaphor.'),
                $this->q(2, 1, 'Corals turn white when they:', ['Build extra limestone.', 'Expel the algae that feed them.', 'Slow waves more effectively.', 'Attract more tourism.'], 1, 'Detail', 'Bleaching explanation.'),
                $this->q(3, 1, 'A rubble field no longer:', ['Supports any coastal town.', 'Protects the coast as a living reef would.', 'Can be visited by divers.', 'Experiences warm water.'], 1, 'Inference', 'Last sentence of A.'),
                $this->q(4, 2, 'Nursery fragments are hung on frames until they:', ['Attract more tourists.', 'Are sturdy enough to attach to rock.', 'Stop needing clean water.', 'Replace all fishing jobs.'], 1, 'Detail', 'Process steps in B.'),
                $this->q(5, 2, 'Village involvement raises survival because:', ['Anchors and fishing pressure are reduced.', 'Scientists leave the site.', 'Heatwaves become shorter.', 'Limestone grows overnight.'], 0, 'Detail', 'Look for “because”.'),
                $this->q(6, 2, 'The writer says restoration is:', ['Instant after one dive.', 'Unnecessary if tourism is strong.', 'Slow, often taking a decade.', 'Impossible without greenhouse cuts only.'], 2, 'Writer’s view', '“The work is slow.”'),
                $this->q(7, 2, 'Healthy fragments are collected by:', ['Shoreline drains.', 'Divers.', 'Boat anchors.', 'Tourist boats only.'], 1, 'Detail', 'First process sentence.'),
                $this->q(8, 3, 'Nurseries alone cannot:', ['Train local divers.', 'Outrun climate change.', 'Rebuild any fish habitat.', 'Keep community attention.'], 1, 'Main idea', 'Opening warning in C.'),
                $this->q(9, 3, 'Which policy is listed alongside restoration?', ['Expanding destructive fishing gear.', 'Ignoring shoreline drains.', 'Cuts in greenhouse gases.', 'Writing reefs off as lost.'], 2, 'Detail', 'List after “beside other policies”.'),
                $this->q(10, 3, 'Local projects are still valued because they:', ['Stop all ocean warming.', 'Rebuild habitat now and train people.', 'Replace national climate policy.', 'Guarantee tourism forever.'], 1, 'Writer’s view', 'Final sentence.'),
                $this->q(11, 3, 'The phrase “written off as lost” suggests reefs might be:', ['Abandoned without local effort.', 'Sold to diving companies.', 'Moved to cooler aquariums.', 'Used only for limestone mining.'], 0, 'Vocabulary in context', 'Contrast with keeping attention on the reef.'),
                $this->q(12, 3, 'The best title is:', ['Why coral tourism should end', 'Restoring reefs while cutting wider pressures', 'How boat anchors build limestone', 'Instant coral forests in one season'], 1, 'Global', 'Nurseries plus climate and fishing policy.'),
                $this->q(13, 3, 'The writer’s position is that restoration:', ['Is a complete climate solution.', 'Works best with cleaner water and lower heat.', 'Should replace greenhouse policy.', 'Cannot help fish habitat at all.'], 1, 'Writer’s view', 'Avoid absolute options.'),
            ],
        ];
    }

    private function q(int $number, int $part, string $prompt, array $options, int $correct, string $tag, string $tip): array
    {
        return [
            'number' => $number,
            'part' => $part,
            'prompt' => $prompt,
            'options' => $options,
            'correct_index' => $correct,
            'skill_tag' => $tag,
            'tip' => $tip,
        ];
    }

    private function mcqPool(): array
    {
        return [
            ['If a bus covers 120 km in 2 hours, its average speed is:', ['40 km/h', '60 km/h', '80 km/h', '100 km/h'], 1, 'Quant', 'Distance ÷ time.'],
            ['Which word is closest in meaning to “concise”?', ['lengthy', 'brief', 'confusing', 'decorative'], 1, 'Verbal', 'Eliminate opposites.'],
            ['If 3x = 24, then x equals:', ['6', '8', '12', '21'], 1, 'Quant', 'Divide both sides by 3.'],
            ['All roses are flowers. Some flowers fade quickly. Which must be true?', ['All flowers are roses.', 'Some roses may fade quickly.', 'No rose is a flower.', 'Roses never fade.'], 1, 'Logic', 'Do not reverse the first statement.'],
            ['A shop offers 20% off a 50-dollar item. The sale price is:', ['$10', '$30', '$40', '$45'], 2, 'Quant', '20% of 50 is 10.'],
            ['The author’s main caution about electric buses is that benefits depend on:', ['Seat color', 'How electricity is generated', 'Ticket design', 'Driver uniforms'], 1, 'Reading', 'Match the energy-source claim.'],
            ['Peer tutoring is said to help:', ['Only tutors', 'Only tutees', 'Both tutors and tutees', 'Neither group'], 2, 'Reading', 'Look for “both partners”.'],
            ['Cash-flow problems often come from:', ['Unpaid invoices and weak forecasts', 'Having too many customers', 'Printing too many receipts', 'Opening on weekends'], 0, 'Business', 'Cause, not a side detail.'],
            ['Which is an odd-one-out?', ['Marshes filter water', 'Wetlands store carbon', 'Paved banks reduce floods', 'Birds rest in restored strips'], 2, 'Inference', 'The passage argues paving makes floods worse.'],
            ['Next number in 2, 6, 12, 20, 30 is:', ['36', '40', '42', '44'], 2, 'Quant', 'Add 4, 6, 8, 10, then 12.'],
            ['“Thrift” is closest to:', ['waste', 'careful saving', 'luxury spending', 'speed'], 1, 'Vocabulary', 'Context of household deposits.'],
            ['If a class of 40 has 15 boys, the percentage of girls is:', ['15%', '37.5%', '62.5%', '75%'], 2, 'Quant', '25 girls out of 40.'],
            ['A good summary should:', ['Copy a side example only', 'Match the central idea', 'Add facts not in the text', 'Use absolute words like “never”'], 1, 'Main idea', 'Avoid extremes.'],
            ['Which option is an extreme absolute?', ['Often', 'Some', 'Always', 'Several'], 2, 'Trap', 'Absolutes are usually wrong in academic MCQ.'],
            ['A lecture on urban heat islands would most likely mention:', ['Concrete absorbing sunlight', 'Deep-sea fishing quotas', 'Medieval poetry', 'Piano scales'], 0, 'Listening', 'Stay with the stated topic.'],
            ['Choose the correctly punctuated sentence:', ['Students notes were lost.', 'Students’ notes were lost.', 'Student notes’ were lost.', 'Students notes’ were lost.'], 1, 'Writing', 'Plural possessive.'],
            ['If 5 workers finish a job in 12 days, 10 workers need:', ['3 days', '6 days', '12 days', '24 days'], 1, 'Quant', 'Double workers, half the time (same rate).'],
            ['The opposite of “scarce” is:', ['rare', 'limited', 'plentiful', 'hidden'], 2, 'Verbal', 'Antonym.'],
            ['A map question usually tests:', ['Memorizing every street name', 'Matching labels to described changes', 'Drawing freehand art', 'Ignoring the legend'], 1, 'Listening', 'Follow the described route.'],
            ['Which is a supporting detail, not a main idea?', ['Wetlands are city infrastructure.', 'One park includes a weekend market.', 'Green space should not be leftover land.', 'Restoration can reduce flood damage.'], 1, 'Main vs detail', 'Examples support; they are not the thesis.'],
            ['15% of 200 is:', ['15', '20', '30', '45'], 2, 'Quant', '10% is 20; 5% is 10.'],
            ['“Hybrid schedules” in offices aim to:', ['Remove all meetings', 'Balance focus time and collaboration', 'Ban documentation', 'End mentoring'], 1, 'Reading', 'Match both sides of the trade-off.'],
            ['If the mean of 4, 8, and x is 8, then x is:', ['4', '8', '12', '16'], 2, 'Quant', 'Sum must be 24.'],
            ['A cue-card answer is stronger when it includes:', ['Only yes/no', 'A definition, a reason, and an example', 'A memorized unrelated story', 'Silence after one sentence'], 1, 'Speaking', 'Cover what / why / example.'],
            ['Microplastics in rivers come from:', ['Only volcanic ash', 'Fibers, packaging, and tire wear', 'Rain alone', 'Fish bones'], 1, 'Detail', 'List in the source text.'],
            ['Which conclusion is too strong for a short academic passage?', ['This policy always fails everywhere.', 'The study suggests a possible benefit.', 'More data would help.', 'Local context may matter.'], 0, 'Inference', 'Avoid “always/everywhere”.'],
            ['A rectangle 8 by 5 has perimeter:', ['13', '26', '40', '80'], 1, 'Quant', '2 × (8+5).'],
            ['Select the best topic sentence:', ['I woke up late.', 'Urban trees lower street temperatures and improve walkability.', 'The bus was yellow.', 'My cousin likes chess.'], 1, 'Writing', 'A topic sentence states the paragraph claim.'],
            ['If today is Wednesday, 10 days later is:', ['Friday', 'Saturday', 'Sunday', 'Monday'], 1, 'Logic', '10 = 7+3 → Wednesday + 3 = Saturday.'],
            ['Open-source software is adopted partly to:', ['Guarantee perfect docs', 'Avoid vendor lock-in', 'Remove all maintainers', 'Ban customization'], 1, 'Reading', 'Eliminate the options the passage rejects.'],
            ['A 3:2 ratio with 30 students means the larger group has:', ['10', '12', '18', '20'], 2, 'Quant', '3+2=5 parts; 3×6=18.'],
            ['Highlight-incorrect tasks require you to:', ['Rewrite the whole lecture', 'Click words that do not match the audio', 'Ignore the transcript', 'Choose a summary paragraph'], 1, 'Listening', 'Compare speech with the written line.'],
            ['The best way to start Task 1 is to:', ['Give your opinion on politics', 'Paraphrase what the chart shows overall', 'Copy the question word for word', 'Invent data points'], 1, 'Writing', 'Overview first, then details.'],
            ['If a = 2b and b = 4, a equals:', ['2', '4', '6', '8'], 3, 'Quant', 'Substitute.'],
            ['Community gardens remain viable when:', ['Land tenure and volunteers are secure', 'Supermarkets close forever', 'Children stop visiting', 'Food miles increase'], 0, 'Detail', 'Condition in the source.'],
            ['Which connector shows contrast?', ['Furthermore', 'However', 'For example', 'In addition'], 1, 'Writing', 'Contrast vs addition.'],
            ['A coin is tossed once. Probability of tails is:', ['0', '1/4', '1/2', '1'], 2, 'Quant', 'Two equally likely outcomes.'],
            ['An interview answer should:', ['Ignore the question', 'Give a short clear reply with one example', 'Speak only in memorized idioms', 'Refuse to pause'], 1, 'Speaking', 'Clarity over decoration.'],
            ['Seagrass meadows help harbors by:', ['Tearing boat propellers', 'Stabilizing mud and storing carbon', 'Increasing dredging', 'Clouding the water on purpose'], 1, 'Reading', 'Match the ecological function.'],
            ['The median of 3, 9, 11 is:', ['3', '9', '11', '23'], 1, 'Quant', 'Middle value when ordered.'],
        ];
    }

    private function extraPteItems(): array
    {
        $images = [
            '/uploads/di-screen-time.svg',
            '/uploads/di-recycled-paper.svg',
            '/uploads/di-campus-map.svg',
        ];
        $items = [];

        $ra = [
            'Compact neighborhoods place schools, shops, and clinics within a short walk, which reduces car trips and gives children more independent mobility.',
            'Public health teams now treat shade as infrastructure, planting street trees where pavement temperatures stay high through the evening.',
            'University archives are digitizing fragile field notebooks so researchers can search historic climate observations without handling the originals.',
            'Harbor speed limits protect seagrass beds that stabilize mud, store carbon, and shelter young fish before they move to deeper water.',
            'Tool-lending libraries let residents borrow drills and sewing machines, cutting waste while teaching practical repair skills.',
            'Forecasting invoices is as important as winning new clients, because unpaid bills can stop a healthy product from covering wages.',
        ];
        foreach ($ra as $i => $text) {
            $items[] = $this->pte([
                'id' => 'x-ra-'.($i + 1),
                'type' => 'read-aloud',
                'section' => 'speaking-writing',
                'title' => 'Read Aloud Extra '.($i + 1),
                'instructions' => 'Look at the text below. In 35 seconds, you must read this text aloud as naturally and clearly as possible.',
                'payload' => ['prep_seconds' => 35, 'record_seconds' => 35, 'text' => $text],
            ]);
        }

        $rs = [
            'Please submit the lab report before Friday afternoon.',
            'The museum opens late on Thursday evenings during summer.',
            'Students should bring a calculator to the statistics workshop.',
            'Night buses will run every twenty minutes after midnight.',
            'The library laptops must be returned to the service desk.',
            'Wetland restoration reduced flooding in the lower neighborhood.',
            'Office teams will meet in person on Tuesdays only.',
            'Tickets for the lecture can be booked on the portal.',
        ];
        foreach ($rs as $i => $text) {
            $items[] = $this->pte([
                'id' => 'x-rs-'.($i + 1),
                'type' => 'repeat-sentence',
                'section' => 'speaking-writing',
                'title' => 'Repeat Sentence Extra '.($i + 1),
                'instructions' => 'You will hear a sentence. Please repeat the sentence exactly as you hear it.',
                'payload' => ['prep_seconds' => 3, 'record_seconds' => 15, 'audio_text' => $text],
            ]);
        }

        $di = [
            ['Average daily screen time by age group (hours)', 'Bar chart comparing ages 10–17, 18–29, 30–44, and 45+'],
            ['How recycled paper is produced', 'Collection → Sorting → Pulping → Cleaning → Rolling → Packaging'],
            ['Campus map changes 2000–2020', 'New library wing, relocated sports field, added tram stop'],
            ['Commuter mode share in a compact city', 'Walk, cycle, bus, and car percentages across two decades'],
        ];
        foreach ($di as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-di-'.($i + 1),
                'type' => 'describe-image',
                'section' => 'speaking-writing',
                'title' => 'Describe Image Extra '.($i + 1),
                'instructions' => 'Look at the graph or diagram. Speak into the microphone and describe in detail what it shows.',
                'payload' => ['prep_seconds' => 25, 'record_seconds' => 40, 'image_title' => $row[0], 'image_hint' => $row[1], 'image_url' => $images[$i % 3]],
                'image_url' => $images[$i % 3],
            ]);
        }

        $lectures = [
            'Today I will talk about rain gardens. These shallow planted basins sit beside streets and parking lots. When rain falls, water slows, soaks into the soil, and nutrients are taken up by plants instead of washing into rivers. Cities use them because they are cheaper than enlarging underground pipes and they create small habitats for insects and birds.',
            'This lecture concerns invoice discipline in small firms. Many owners celebrate a signed contract and forget that cash arrives weeks later. A simple weekly forecast of what will be paid, plus clear due dates on every invoice, prevents the surprise that wages cannot be met even when sales look strong.',
        ];
        foreach ($lectures as $i => $text) {
            $items[] = $this->pte([
                'id' => 'x-rl-'.($i + 1),
                'type' => 'retell-lecture',
                'section' => 'speaking-writing',
                'title' => 'Re-tell Lecture Extra '.($i + 1),
                'instructions' => 'You will hear a lecture. After listening, in 10 seconds, please speak into the microphone and retell what you have just heard in your own words.',
                'payload' => ['prep_seconds' => 10, 'record_seconds' => 40, 'audio_text' => $text],
            ]);
        }

        $asq = [
            ['What do bees produce in a hive?', 'Honey', 'honey'],
            ['What is the capital city of Australia?', 'Canberra', 'canberra'],
            ['What instrument is used to measure temperature?', 'A thermometer', 'thermometer'],
            ['What is frozen water called?', 'Ice', 'ice'],
            ['How many minutes are there in one hour?', 'Sixty', 'sixty'],
            ['What do we call a place that lends books for free?', 'A library', 'library'],
        ];
        foreach ($asq as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-asq-'.($i + 1),
                'type' => 'answer-short',
                'section' => 'speaking-writing',
                'title' => 'Answer Short Extra '.($i + 1),
                'instructions' => 'You will hear a question. Please give a simple and short answer.',
                'payload' => ['prep_seconds' => 2, 'record_seconds' => 10, 'audio_text' => $row[0], 'prompt' => $row[0]],
                'correct_text' => $row[1],
                'answer_key' => ['kind' => 'text', 'value' => $row[2]],
            ]);
        }

        $swt = [
            'Street trees are often planted for beauty, yet their greatest urban value is shade. Pavement that sits in sun can be several degrees hotter than a path under a canopy, which raises air-conditioning demand in nearby apartments. Trees also slow stormwater and capture some airborne particles. Cities that treat planting as infrastructure, with watering plans and space for roots, keep more trees alive past the first dry summer.',
            'Peer tutoring works when sessions are short and regular. Tutors strengthen their own understanding by explaining a single idea, while tutees ask questions they might avoid in a lecture. Staff still review notes so errors are not rehearsed. Programs that skip this check save time at first and then spread misconceptions.',
            'Harbor seagrass looks like empty mud from a boat, but the roots lock sediment and store carbon. Fast propellers tear the beds and cloud the water, blocking light the plants need. Marked slow-speed channels let ports keep working while the meadows recover, which in turn supports young fish.',
            'Libraries that lend tools and host coding clubs are responding to a simple gap: many households cannot buy equipment they need only once. Shared access reduces waste and builds skills. The model depends on staff who can repair items and on opening hours that match working residents, not only students.',
        ];
        foreach ($swt as $i => $text) {
            $items[] = $this->pte([
                'id' => 'x-swt-'.($i + 1),
                'type' => 'swt',
                'section' => 'speaking-writing',
                'title' => 'Summarize Written Text Extra '.($i + 1),
                'instructions' => 'Read the passage below and summarize it using one sentence. You have 10 minutes. Write 5–75 words.',
                'payload' => ['time_limit_seconds' => 600, 'word_limit' => ['min' => 5, 'max' => 75], 'text' => $text],
            ]);
        }

        $essays = [
            'Some cities argue that new housing should always take priority over parks, while others say green space is essential infrastructure. Discuss both views and give your own opinion.',
            'Do the advantages of night-shift work in hospitals outweigh the health costs for staff? Support your position with reasons and examples.',
            'Some people believe exams should be replaced by continuous coursework. Others say timed tests are fairer. Discuss both views and give your opinion.',
            'To what extent should governments fund coral restoration if climate change continues to warm the seas?',
        ];
        foreach ($essays as $i => $prompt) {
            $items[] = $this->pte([
                'id' => 'x-essay-'.($i + 1),
                'type' => 'essay',
                'section' => 'speaking-writing',
                'title' => 'Write Essay Extra '.($i + 1),
                'instructions' => 'You will have 20 minutes to plan, write and revise an essay. Write 200–300 words.',
                'payload' => ['time_limit_seconds' => 1200, 'word_limit' => ['min' => 200, 'max' => 300], 'prompt' => $prompt],
            ]);
        }

        $rw = [
            [
                ['Street', 'trees', 'lower', 'pavement', '___1___', 'and', 'help', 'apartments', 'use', 'less', '___2___', 'in', 'summer.'],
                [['heat', 'music', 'traffic', 'silence'], ['cooling', 'postage', 'fiction', 'cement']],
                ['heat', 'cooling'],
            ],
            [
                ['Invoice', '___1___', 'prevents', 'wage', 'shortfalls', 'even', 'when', '___2___', 'looks', 'strong.'],
                [['discipline', 'decoration', 'delay', 'distance'], ['demand', 'dust', 'drama', 'danger']],
                ['discipline', 'demand'],
            ],
            [
                ['Seagrass', 'roots', '___1___', 'harbor', 'mud', 'and', '___2___', 'carbon', 'out', 'of', 'the', 'water.'],
                [['stabilize', 'scatter', 'sell', 'skip'], ['store', 'spill', 'stir', 'sort']],
                ['stabilize', 'store'],
            ],
            [
                ['Peer', 'tutoring', 'works', 'when', 'sessions', 'stay', '___1___', 'and', 'staff', '___2___', 'the', 'explanations.'],
                [['regular', 'random', 'royal', 'rusty'], ['check', 'charge', 'chase', 'cheer']],
                ['regular', 'check'],
            ],
        ];
        foreach ($rw as $i => $row) {
            $items[] = $this->blankItem('x-rwfib-'.($i + 1), 'rw-fib', 'reading', 'R&W Fill in the Blanks Extra '.($i + 1), $row[0], $row[1], $row[2]);
        }

        $rfib = [
            [
                ['Rain', 'gardens', 'let', 'stormwater', '___1___', 'slowly', 'so', 'plants', 'can', 'take', 'up', '___2___', 'before', 'it', 'reaches', 'rivers.'],
                ['soak', 'jump', 'print', 'argue', 'nutrients', 'tickets'],
                ['soak', 'nutrients'],
            ],
            [
                ['Coral', 'fragments', 'grow', 'on', 'frames', 'until', 'they', 'are', '___1___', 'enough', 'to', 'attach', 'to', '___2___', 'rock.'],
                ['sturdy', 'silent', 'sudden', 'damaged', 'empty', 'yellow'],
                ['sturdy', 'damaged'],
            ],
        ];
        foreach ($rfib as $i => $row) {
            $blanks = [
                ['id' => '1', 'choices' => array_slice($row[1], 0, 3)],
                ['id' => '2', 'choices' => array_slice($row[1], 3, 3)],
            ];
            $items[] = $this->pte([
                'id' => 'x-rfib-'.($i + 1),
                'type' => 'r-fib',
                'section' => 'reading',
                'title' => 'Reading Fill in the Blanks Extra '.($i + 1),
                'instructions' => 'In the text below some words are missing. Drag words from the box to the appropriate place.',
                'payload' => ['passage_tokens' => $row[0], 'word_bank' => $row[1], 'blanks' => $blanks],
                'answer_key' => ['kind' => 'blanks', 'values' => $row[2]],
            ]);
        }

        $mcma = [
            [
                'Rain gardens slow stormwater, take up nutrients, and create small habitats. They are cheaper than enlarging pipes, though they need space beside streets and regular weeding.',
                'Which benefits of rain gardens are mentioned?',
                ['They slow stormwater', 'They remove the need for any plants', 'They can be cheaper than bigger pipes', 'They create small habitats', 'They eliminate all city flooding forever'],
                [0, 2, 3],
            ],
            [
                'Night-shift hospitals use brighter work light, dark rest rooms, and limits on consecutive nights. Caffeine is only a short mask for tiredness.',
                'According to the text, safer night work can include:',
                ['Brighter light during the working night', 'Unlimited consecutive nights', 'Dark rooms for naps', 'Treating caffeine as a full sleep replacement', 'Limits on consecutive nights'],
                [0, 2, 4],
            ],
        ];
        foreach ($mcma as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-mcma-'.($i + 1),
                'type' => 'mc-multi',
                'section' => 'reading',
                'title' => 'Reading MCQ Multi Extra '.($i + 1),
                'instructions' => 'Select all the correct responses. More than one response is correct.',
                'payload' => ['multi' => true, 'text' => $row[0], 'prompt' => $row[1], 'options' => $row[2]],
                'answer_key' => ['kind' => 'multi', 'indexes' => $row[3]],
            ]);
        }

        $mcsa = [
            [
                'Tool-lending libraries cut waste because households borrow equipment they need only once, but the model needs staff who can repair broken items.',
                'What does the author emphasize?',
                ['Lending works without any staff', 'Repair skills are part of a working lending library', 'Households should buy every tool', 'Libraries should stop lending books'],
                1,
            ],
            [
                'Seagrass recovery in harbors depends on slow-speed channels; closing an entire port is not required if boats stay in marked paths.',
                'The writer suggests that ports can:',
                ['Ignore seagrass entirely', 'Keep operating with slower marked channels', 'Ban all shipping forever', 'Replace plants with extra dredging'],
                1,
            ],
        ];
        foreach ($mcsa as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-mcsa-'.($i + 1),
                'type' => 'mc-single',
                'section' => 'reading',
                'title' => 'Reading MCQ Single Extra '.($i + 1),
                'instructions' => 'Select the correct response. Only one response is correct.',
                'payload' => ['text' => $row[0], 'prompt' => $row[1], 'options' => $row[2]],
                'answer_key' => ['kind' => 'single', 'index' => $row[3]],
            ]);
        }

        $reorder = [
            [
                ['a' => 'First, the city maps pavement temperatures on a hot afternoon.', 'b' => 'Planners then mark the hottest corridors for new street trees.', 'c' => 'After two summers, walking surveys check whether shade has improved.', 'd' => 'Watering crews are assigned so young trees survive the first dry season.'],
                ['a', 'b', 'd', 'c'],
            ],
            [
                ['a' => 'Coral fragments are clipped from healthy colonies.', 'b' => 'Divers cement the sturdy pieces onto damaged reef rock.', 'c' => 'Fish begin to return as the branching structure grows.', 'd' => 'The fragments hang on frames until they thicken.'],
                ['a', 'd', 'b', 'c'],
            ],
        ];
        foreach ($reorder as $i => $row) {
            $paragraphs = [];
            foreach ($row[0] as $id => $text) {
                $paragraphs[] = ['id' => $id, 'text' => $text];
            }
            $items[] = $this->pte([
                'id' => 'x-ro-'.($i + 1),
                'type' => 'reorder',
                'section' => 'reading',
                'title' => 'Re-order Paragraphs Extra '.($i + 1),
                'instructions' => 'Restore the original order by moving the paragraphs.',
                'payload' => ['paragraphs' => $paragraphs],
                'answer_key' => ['kind' => 'order', 'ids' => $row[1]],
            ]);
        }

        $sst = [
            'In this talk I discuss rain gardens beside city streets. They are shallow planted basins that slow stormwater so soil and plants can take up nutrients before the water reaches rivers. Engineers like them because they cost less than enlarging underground pipes. They also create small habitats. The main requirement is leftover space along the curb and a plan to weed and water the plants in the first dry years.',
            'This lecture is about night-shift safety. Human biology expects darkness for repair, so error rates often rise before dawn. Hospitals that add brighter work lighting, fully dark rest rooms, and limits on consecutive nights report fewer mistakes. Caffeine can hide tiredness briefly but cannot replace a protected sleep window after the shift.',
        ];
        foreach ($sst as $i => $text) {
            $items[] = $this->pte([
                'id' => 'x-sst-'.($i + 1),
                'type' => 'sst',
                'section' => 'listening',
                'title' => 'Summarize Spoken Text Extra '.($i + 1),
                'instructions' => 'Write a 50–70 word summary of the lecture. You have 10 minutes.',
                'payload' => ['time_limit_seconds' => 600, 'word_limit' => ['min' => 50, 'max' => 70], 'audio_text' => $text],
            ]);
        }

        $lmcma = [
            [
                'The field workshop includes a wetland walk, a soil-sampling task, and a short briefing at the visitor center. Transport is provided; lunch is not.',
                'What is included in the workshop?',
                ['A wetland walk', 'Soil sampling', 'A free lunch', 'A visitor-center briefing'],
                [0, 1, 3],
            ],
            [
                'From next month the night bus will run every twenty minutes until 2 a.m. on Fridays and Saturdays only. Student cards still receive the usual discount.',
                'Which statements are true?',
                ['Night buses run more often on Friday and Saturday', 'The change starts next month', 'The extra frequency applies every weekday', 'Student discounts continue'],
                [0, 1, 3],
            ],
        ];
        foreach ($lmcma as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-lmcma-'.($i + 1),
                'type' => 'l-mc-multi',
                'section' => 'listening',
                'title' => 'Listening MCQ Multi Extra '.($i + 1),
                'instructions' => 'Select all the correct responses.',
                'payload' => ['multi' => true, 'audio_text' => $row[0], 'prompt' => $row[1], 'options' => $row[2]],
                'answer_key' => ['kind' => 'multi', 'indexes' => $row[3]],
            ]);
        }

        $lfib = [
            ['The rain garden will be planted beside the library next spring, and volunteers can sign up online.', ['The', 'rain', 'garden', 'will', 'be', 'planted', 'beside', 'the', '___', 'next', 'spring,', 'and', 'volunteers', 'can', 'sign', 'up', '___.'], 'library online'],
            ['Night buses will run every twenty minutes after midnight on Friday.', ['Night', 'buses', 'will', 'run', 'every', 'twenty', '___', 'after', 'midnight', 'on', '___.'], 'minutes Friday'],
        ];
        foreach ($lfib as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-lfib-'.($i + 1),
                'type' => 'l-fib',
                'section' => 'listening',
                'title' => 'Listening Fill in the Blanks Extra '.($i + 1),
                'instructions' => 'Type the missing words in each blank.',
                'payload' => ['audio_text' => $row[0], 'passage_tokens' => $row[1]],
                'correct_text' => $row[2],
                'answer_key' => ['kind' => 'text', 'value' => $row[2]],
            ]);
        }

        $hcs = [
            [
                'Tool-lending libraries work because people borrow equipment they need once, which cuts waste, but staff must repair broken tools and keep evening hours for workers.',
                ['Libraries should stop lending books and only sell new tools.', 'Shared tools reduce waste if staff repair items and stay open for working residents.', 'Tool libraries fail unless every household buys a drill.', 'Evening hours are unnecessary if students visit at noon.'],
                1,
            ],
            [
                'Coral nurseries can rebuild local fish habitat, yet they cannot outrun warming seas unless greenhouse gases also fall and shoreline water stays cleaner.',
                ['Nurseries alone will reverse global climate change.', 'Local restoration still helps, but it must sit beside cleaner water and climate policy.', 'Fishing pressure has no effect on fragment survival.', 'Restored reefs appear in a single weekend.'],
                1,
            ],
        ];
        foreach ($hcs as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-hcs-'.($i + 1),
                'type' => 'highlight-summary',
                'section' => 'listening',
                'title' => 'Highlight Correct Summary Extra '.($i + 1),
                'instructions' => 'Click on the paragraph that best relates to the recording.',
                'payload' => ['audio_text' => $row[0], 'options' => $row[1]],
                'answer_key' => ['kind' => 'single', 'index' => $row[2]],
            ]);
        }

        $lmcsa = [
            [
                'The statistics workshop moves to Friday morning in Room 4B. Bring a calculator; laptops are optional.',
                'What should students bring?',
                ['A packed lunch only', 'A calculator', 'Printed tickets', 'Nothing at all'],
                1,
            ],
            [
                'Harbor slow-speed zones start at the red marker and continue to the marina entrance. They apply to all boats, including water taxis.',
                'Who must follow the slow-speed zone?',
                ['Only sailing clubs', 'All boats, including water taxis', 'Trucks on the bridge', 'Swimmers only'],
                1,
            ],
        ];
        foreach ($lmcsa as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-lmcsa-'.($i + 1),
                'type' => 'l-mc-single',
                'section' => 'listening',
                'title' => 'Listening MCQ Single Extra '.($i + 1),
                'instructions' => 'Select the correct response. Only one response is correct.',
                'payload' => ['audio_text' => $row[0], 'prompt' => $row[1], 'options' => $row[2]],
                'answer_key' => ['kind' => 'single', 'index' => $row[3]],
            ]);
        }

        $smw = [
            [
                'Street trees survive past the first summer only if the city funds a plan for',
                ['watering', 'wallpaper', 'whistling', 'warehouses'],
                0,
            ],
            [
                'After the night shift, staff need a protected window for',
                ['sleep', 'sales', 'cement', 'scenery'],
                0,
            ],
        ];
        foreach ($smw as $i => $row) {
            $items[] = $this->pte([
                'id' => 'x-smw-'.($i + 1),
                'type' => 'missing-word',
                'section' => 'listening',
                'title' => 'Select Missing Word Extra '.($i + 1),
                'instructions' => 'The last word has been replaced by a beep. Select the correct option.',
                'payload' => ['audio_text' => $row[0], 'options' => $row[1]],
                'answer_key' => ['kind' => 'single', 'index' => $row[2]],
            ]);
        }

        $hiw = [
            ['The laboratory will test water samples collected near the harbor this morning.', 'library', 'railway'],
            ['Please return the laptops to the service desk before closing time.', 'textbooks', 'opening'],
        ];
        foreach ($hiw as $i => $row) {
            $spoken = explode(' ', $row[0]);
            $transcript = [];
            $incorrect = [$row[1], $row[2]];
            $swap = 0;
            foreach ($spoken as $idx => $word) {
                $clean = rtrim($word, '.,');
                if ($idx === 1 || $idx === count($spoken) - 3) {
                    $transcript[] = ['word' => $incorrect[$swap] ?? $word, 'incorrect' => true];
                    $swap++;
                } else {
                    $transcript[] = ['word' => $word];
                }
            }
            $items[] = $this->pte([
                'id' => 'x-hiw-'.($i + 1),
                'type' => 'highlight-incorrect',
                'section' => 'listening',
                'title' => 'Highlight Incorrect Words Extra '.($i + 1),
                'instructions' => 'Click on the words that differ from the recording.',
                'payload' => ['audio_text' => $row[0], 'transcript_with_errors' => $transcript],
                'answer_key' => ['kind' => 'hiw', 'words' => $incorrect],
            ]);
        }

        $wfd = [
            'The rain garden will be planted beside the library next spring.',
            'Please keep a protected sleep window after the night shift.',
            'Harbor boats must stay inside the marked slow-speed channel.',
            'Invoice forecasts should be updated at the start of every week.',
            'Students may borrow laptops from the campus library desk.',
            'Coral fragments remain on frames until they are sturdy enough.',
        ];
        foreach ($wfd as $i => $text) {
            $items[] = $this->pte([
                'id' => 'x-wfd-'.($i + 1),
                'type' => 'wfd',
                'section' => 'listening',
                'title' => 'Write From Dictation Extra '.($i + 1),
                'instructions' => 'Type the sentence exactly as you hear it.',
                'payload' => ['audio_text' => $text],
                'correct_text' => $text,
                'answer_key' => ['kind' => 'text', 'value' => strtolower($text)],
            ]);
        }

        return array_merge($items, $this->generatedPteItems());
    }

    private function generatedPteItems(): array
    {
        $images = [
            '/uploads/di-screen-time.svg',
            '/uploads/di-recycled-paper.svg',
            '/uploads/di-campus-map.svg',
        ];
        $titles = $this->titles();
        $passages = $this->passages();
        $items = [];

        for ($i = 0; $i < 24; $i++) {
            $text = $this->passageFor(0, $i + 40, $passages);
            $items[] = $this->pte([
                'id' => 'g-ra-'.($i + 1),
                'type' => 'read-aloud',
                'section' => 'speaking-writing',
                'title' => 'Read Aloud Pack '.($i + 1),
                'instructions' => 'Look at the text below. In 35 seconds, you must read this text aloud as naturally and clearly as possible.',
                'payload' => ['prep_seconds' => 35, 'record_seconds' => 35, 'text' => $text],
            ]);
        }

        for ($i = 0; $i < 24; $i++) {
            $topic = $titles[$i % count($titles)];
            $text = 'Please review the notes on '.strtolower($topic).' before Friday afternoon.';
            $items[] = $this->pte([
                'id' => 'g-rs-'.($i + 1),
                'type' => 'repeat-sentence',
                'section' => 'speaking-writing',
                'title' => 'Repeat Sentence Pack '.($i + 1),
                'instructions' => 'You will hear a sentence. Please repeat the sentence exactly as you hear it.',
                'payload' => ['prep_seconds' => 3, 'record_seconds' => 15, 'audio_text' => $text],
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            $topic = $titles[($i * 3) % count($titles)];
            $items[] = $this->pte([
                'id' => 'g-di-'.($i + 1),
                'type' => 'describe-image',
                'section' => 'speaking-writing',
                'title' => 'Describe Image Pack '.($i + 1),
                'instructions' => 'Look at the graph or diagram. Speak into the microphone and describe in detail what it shows.',
                'payload' => [
                    'prep_seconds' => 25,
                    'record_seconds' => 40,
                    'image_title' => $topic.' over two decades',
                    'image_hint' => 'Compare the highest value, a decline, and the overall trend.',
                    'image_url' => $images[$i % 3],
                ],
                'image_url' => $images[$i % 3],
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            $text = $this->passageFor(2, $i + 20, $passages);
            $items[] = $this->pte([
                'id' => 'g-rl-'.($i + 1),
                'type' => 'retell-lecture',
                'section' => 'speaking-writing',
                'title' => 'Re-tell Lecture Pack '.($i + 1),
                'instructions' => 'You will hear a lecture. After listening, in 10 seconds, please speak into the microphone and retell what you have just heard in your own words.',
                'payload' => ['prep_seconds' => 10, 'record_seconds' => 40, 'audio_text' => $text],
            ]);
        }

        $asq = [
            ['What do coral animals build?', 'Limestone skeletons', 'limestone'],
            ['What gas do plants take in?', 'Carbon dioxide', 'carbon dioxide'],
            ['What is the opposite of urban?', 'Rural', 'rural'],
            ['How many days are in a leap year?', 'Three hundred and sixty-six', '366'],
            ['What organ pumps blood?', 'The heart', 'heart'],
            ['What do we call water in gas form?', 'Steam', 'steam'],
            ['What planet is known as the red planet?', 'Mars', 'mars'],
            ['What is the main gas in air?', 'Nitrogen', 'nitrogen'],
            ['What do bees collect from flowers?', 'Nectar', 'nectar'],
            ['What is a baby cat called?', 'A kitten', 'kitten'],
            ['What scale measures earthquakes?', 'The Richter scale', 'richter'],
            ['What is H2O commonly called?', 'Water', 'water'],
            ['What instrument shows direction?', 'A compass', 'compass'],
            ['What do we call a doctor for animals?', 'A veterinarian', 'vet'],
            ['How many continents are there?', 'Seven', 'seven'],
            ['What is the largest ocean?', 'The Pacific', 'pacific'],
            ['What vitamin comes mainly from sunlight?', 'Vitamin D', 'vitamin d'],
            ['What is the boiling point of water in Celsius?', 'One hundred', '100'],
            ['What do we call a group of lions?', 'A pride', 'pride'],
            ['What metal is liquid at room temperature?', 'Mercury', 'mercury'],
            ['What is the capital of Japan?', 'Tokyo', 'tokyo'],
            ['What force pulls objects toward Earth?', 'Gravity', 'gravity'],
            ['What is the study of living things?', 'Biology', 'biology'],
            ['What do we call frozen rain?', 'Hail', 'hail'],
        ];
        foreach ($asq as $i => $row) {
            $items[] = $this->pte([
                'id' => 'g-asq-'.($i + 1),
                'type' => 'answer-short',
                'section' => 'speaking-writing',
                'title' => 'Answer Short Pack '.($i + 1),
                'instructions' => 'You will hear a question. Please give a simple and short answer.',
                'payload' => ['prep_seconds' => 2, 'record_seconds' => 10, 'audio_text' => $row[0], 'prompt' => $row[0]],
                'correct_text' => $row[1],
                'answer_key' => ['kind' => 'text', 'value' => $row[2]],
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            $text = $this->passageFor(3, $i + 8, $passages);
            $items[] = $this->pte([
                'id' => 'g-swt-'.($i + 1),
                'type' => 'swt',
                'section' => 'speaking-writing',
                'title' => 'Summarize Written Text Pack '.($i + 1),
                'instructions' => 'Read the passage below and summarize it using one sentence. You have 10 minutes. Write 5–75 words.',
                'payload' => ['time_limit_seconds' => 600, 'word_limit' => ['min' => 5, 'max' => 75], 'text' => $text],
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            $topic = $titles[($i * 4 + 2) % count($titles)];
            $prompt = "Some people say {$topic} should be funded by government, while others prefer private action. Discuss both views and give your own opinion.";
            $items[] = $this->pte([
                'id' => 'g-essay-'.($i + 1),
                'type' => 'essay',
                'section' => 'speaking-writing',
                'title' => 'Write Essay Pack '.($i + 1),
                'instructions' => 'You will have 20 minutes to plan, write and revise an essay. Write 200–300 words.',
                'payload' => ['time_limit_seconds' => 1200, 'word_limit' => ['min' => 200, 'max' => 300], 'prompt' => $prompt],
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            $text = $this->passageFor(4, $i + 12, $passages);
            $items[] = $this->pte([
                'id' => 'g-sst-'.($i + 1),
                'type' => 'sst',
                'section' => 'listening',
                'title' => 'Summarize Spoken Text Pack '.($i + 1),
                'instructions' => 'Write a 50–70 word summary of the lecture. You have 10 minutes.',
                'payload' => ['time_limit_seconds' => 600, 'word_limit' => ['min' => 50, 'max' => 70], 'audio_text' => $text],
            ]);
        }

        for ($i = 0; $i < 16; $i++) {
            $topic = strtolower($titles[($i * 2 + 1) % count($titles)]);
            $text = 'The briefing on '.$topic.' will be updated at the start of every week.';
            $items[] = $this->pte([
                'id' => 'g-wfd-'.($i + 1),
                'type' => 'wfd',
                'section' => 'listening',
                'title' => 'Write From Dictation Pack '.($i + 1),
                'instructions' => 'Type the sentence exactly as you hear it.',
                'payload' => ['audio_text' => $text],
                'correct_text' => $text,
                'answer_key' => ['kind' => 'text', 'value' => strtolower($text)],
            ]);
        }

        return $items;
    }

    private function blankItem(string $id, string $type, string $section, string $title, array $tokens, array $choiceSets, array $values): array
    {
        $blanks = [];
        foreach ($choiceSets as $i => $choices) {
            $blanks[] = ['id' => (string) ($i + 1), 'choices' => $choices];
        }

        return $this->pte([
            'id' => $id,
            'type' => $type,
            'section' => $section,
            'title' => $title,
            'instructions' => 'Click on each blank and select the appropriate word.',
            'payload' => ['passage_tokens' => $tokens, 'blanks' => $blanks],
            'answer_key' => ['kind' => 'blanks', 'values' => $values],
        ]);
    }

    private function pte(array $row): array
    {
        return [
            'external_id' => $row['id'],
            'type' => $row['type'],
            'section' => $row['section'],
            'title' => $row['title'],
            'instructions' => $row['instructions'],
            'payload' => $row['payload'],
            'image_url' => $row['image_url'] ?? null,
            'correct_text' => $row['correct_text'] ?? null,
            'answer_key' => $row['answer_key'] ?? null,
        ];
    }
}
