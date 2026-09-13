<?php

namespace App\Support\Cms;

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
use App\Models\Skill;
use App\Models\TaskType;
use App\Support\PracticePayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Repository
{
    public static function list(string $key): Collection
    {
        return self::query($key)->map(fn ($row) => self::toForm($key, $row));
    }

    public static function find(string $key, string $id): ?array
    {
        $row = self::findModel($key, $id);

        return $row ? self::toForm($key, $row) : null;
    }

    public static function save(string $key, array $data, bool $isNew): array
    {
        $id = (string) ($data['id'] ?? $data['label'] ?? '');
        if ($id === '' && $key !== 'readingPassage') {
            throw new InvalidArgumentException('An id is required.');
        }

        $model = $isNew ? null : self::findModel($key, $id);
        if (! $isNew && ! $model) {
            throw new InvalidArgumentException('Item not found.');
        }
        if ($isNew && $model) {
            throw new InvalidArgumentException('That id already exists.');
        }

        return self::toForm($key, self::persist($key, $model, $data, $isNew));
    }

    public static function delete(string $key, string $id): void
    {
        $model = self::findModel($key, $id);
        if ($model) {
            if ($key === 'courses') {
                $model->modules()->delete();
            }
            $model->delete();
        }
    }

    public static function options(string $from): array
    {
        return match ($from) {
            'exams' => Exam::query()->orderBy('id')->get()->map(fn ($e) => ['value' => $e->slug, 'label' => $e->short_name])->all(),
            'skills' => Skill::query()->orderBy('sort_order')->get()->map(fn ($s) => ['value' => $s->slug, 'label' => $s->label])->all(),
            'tasks' => TaskType::query()->orderBy('name')->get()->map(fn ($t) => ['value' => $t->slug, 'label' => $t->name])->all(),
            'pteTypes' => PteTaskType::query()->orderBy('label')->get()->map(fn ($t) => ['value' => $t->slug, 'label' => $t->label])->all(),
            'mockSections' => MockSection::query()->orderBy('id')->get()->map(fn ($s) => ['value' => $s->slug, 'label' => $s->label])->all(),
            'certCategories' => CertificateCategory::query()->orderBy('id')->get()->map(fn ($c) => ['value' => $c->slug, 'label' => $c->label])->all(),
            'pteSections' => [
                ['value' => 'speaking-writing', 'label' => 'Speaking & Writing'],
                ['value' => 'reading', 'label' => 'Reading'],
                ['value' => 'listening', 'label' => 'Listening'],
            ],
            default => [],
        };
    }

    public static function counts(): array
    {
        $out = [];
        foreach (Collections::definitions() as $def) {
            $out[$def['key']] = self::query($def['key'])->count();
        }
        $out['media'] = MediaItem::query()->count();

        return $out;
    }

    public static function resolveHref(?string $template, array $item): ?string
    {
        if (! $template) {
            return null;
        }

        return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($item) {
            return urlencode((string) ($item[$m[1]] ?? ''));
        }, $template);
    }

    private static function query(string $key): Collection
    {
        return match ($key) {
            'exams' => Exam::query()->orderBy('id')->get(),
            'courses' => Course::query()->with('modules')->orderBy('sort_order')->get(),
            'skills' => Skill::query()->orderBy('sort_order')->get(),
            'tasks' => TaskType::query()->orderBy('id')->get(),
            'questions' => PracticeQuestion::query()->orderBy('id')->get(),
            'mocks' => MockTest::query()->orderBy('id')->get(),
            'mockSections' => MockSection::query()->orderBy('id')->get(),
            'certificates' => Certificate::query()->orderBy('id')->get(),
            'certCategories' => CertificateCategory::query()->orderBy('id')->get(),
            'pteQuestions' => PteQuestion::query()->orderBy('sort_order')->get(),
            'pteTaskTypes' => PteTaskType::query()->orderBy('id')->get(),
            'diagnostics' => DiagnosticQuestion::query()->orderBy('sort_order')->get(),
            'readingPassage' => self::readingPassages(),
            'readingQuestions' => self::readingQuestions(),
            'media' => MediaItem::query()->latest()->get(),
            default => collect(),
        };
    }

    private static function findModel(string $key, string $id)
    {
        return match ($key) {
            'exams' => Exam::query()->where('slug', $id)->first(),
            'courses' => Course::query()->with('modules')->where('slug', $id)->first(),
            'skills' => Skill::query()->where('slug', $id)->first(),
            'tasks' => TaskType::query()->where('slug', $id)->first(),
            'questions' => PracticeQuestion::query()->where('external_id', $id)->first(),
            'mocks' => MockTest::query()->where('slug', $id)->first(),
            'mockSections' => MockSection::query()->where('slug', $id)->first(),
            'certificates' => Certificate::query()->where('slug', $id)->first(),
            'certCategories' => CertificateCategory::query()->where('slug', $id)->first(),
            'pteQuestions' => PteQuestion::query()->where('external_id', $id)->first(),
            'pteTaskTypes' => PteTaskType::query()->where('slug', $id)->first(),
            'diagnostics' => DiagnosticQuestion::query()->where('external_id', $id)->first(),
            'readingPassage' => MockPassage::query()->find($id) ?? MockPassage::query()->where('label', $id)->first(),
            'readingQuestions' => MockQuestion::query()
                ->where('number', (int) $id)
                ->where('mock_test_id', self::readingMockId())
                ->first(),
            'media' => MediaItem::query()->where('external_id', $id)->first(),
            default => null,
        };
    }

    private static function persist(string $key, $model, array $data, bool $isNew)
    {
        return match ($key) {
            'exams' => self::saveExam($model, $data),
            'courses' => self::saveCourse($model, $data),
            'skills' => self::saveSkill($model, $data),
            'tasks' => self::saveTask($model, $data),
            'questions' => self::saveQuestion($model, $data),
            'mocks' => self::saveMock($model, $data),
            'mockSections' => self::saveMockSection($model, $data),
            'certificates' => self::saveCertificate($model, $data),
            'certCategories' => self::saveCertCategory($model, $data),
            'pteQuestions' => self::savePte($model, $data),
            'pteTaskTypes' => self::savePteType($model, $data),
            'diagnostics' => self::saveDiagnostic($model, $data),
            'readingPassage' => self::savePassage($model, $data),
            'readingQuestions' => self::saveReadingQuestion($model, $data),
            default => throw new InvalidArgumentException('Unknown collection'),
        };
    }

    public static function toForm(string $key, $row): array
    {
        return match ($key) {
            'exams' => [
                'id' => $row->slug,
                'name' => $row->name,
                'shortName' => $row->short_name,
                'tagline' => $row->tagline,
                'accent' => $row->accent,
                'accentSoft' => $row->accent_soft,
                'description' => $row->description,
                'questionCount' => $row->question_count,
                'freePractice' => (bool) $row->free_practice,
                'coverImage' => $row->cover_image ?? '',
                'icon' => $row->icon ?? 'GraduationCap',
            ],
            'courses' => [
                'id' => $row->slug,
                'name' => $row->name,
                'tag' => $row->tag,
                'color' => $row->color,
                'soft' => $row->soft,
                'glow' => $row->glow,
                'total' => $row->total,
                'coverImage' => $row->cover_image ?? '',
                'modules' => $row->modules->map(fn ($m) => [
                    'name' => $m->name,
                    'count' => $m->count_label,
                    'value' => $m->value,
                    'icon' => $m->icon,
                ])->all(),
            ],
            'skills' => [
                'id' => $row->slug,
                'label' => $row->label,
                'color' => $row->color,
                'soft' => $row->soft,
                'icon' => $row->icon,
            ],
            'tasks' => [
                'id' => $row->slug,
                'name' => $row->name,
                'skill' => $row->skill,
                'examIds' => $row->exam_slugs ?? [],
                'done' => $row->done,
                'total' => $row->total,
            ],
            'questions' => QuestionEntry::expand([
                'id' => $row->external_id,
                'title' => $row->title,
                'taskId' => $row->task_slug,
                'examIds' => $row->exam_slugs ?? [],
                'difficulty' => $row->difficulty,
                'appeared' => $row->appeared,
                'isPrediction' => (bool) $row->is_prediction,
                'practiced' => (bool) $row->practiced,
                'bookmarked' => (bool) $row->bookmarked,
                'imageUrl' => $row->image_url ?? '',
                'prompt' => $row->prompt ?? '',
                'passage' => $row->passage ?? '',
                'audioText' => $row->meta['audio_text'] ?? '',
                'prepSeconds' => $row->meta['prep_seconds'] ?? 30,
                'recordSeconds' => $row->meta['record_seconds'] ?? 35,
                'wordLimitMin' => $row->meta['word_limit']['min'] ?? '',
                'wordLimitMax' => $row->meta['word_limit']['max'] ?? '',
                'options' => $row->meta['options'] ?? [],
                'correctIndex' => $row->meta['answer_key']['index'] ?? '',
                'correctText' => $row->meta['correct_text'] ?? '',
                'sampleAnswer' => $row->meta['sample_answer'] ?? '',
                'wordBank' => $row->meta['word_bank'] ?? [],
                'blanks' => $row->meta['blanks'] ?? [],
                'passageTokens' => $row->meta['passage_tokens'] ?? [],
                'paragraphs' => $row->meta['paragraphs'] ?? [],
                'transcript' => $row->meta['transcript_with_errors'] ?? [],
                'answerKey' => $row->meta['answer_key'] ?? [],
            ], (string) $row->task_slug),
            'mocks' => [
                'id' => $row->slug,
                'title' => $row->title,
                'subtitle' => $row->subtitle,
                'examId' => $row->exam_slug,
                'section' => $row->section,
                'coverImage' => $row->cover_image ?? '',
                'durationMin' => $row->duration_min,
                'questions' => $row->questions,
                'attempts' => $row->attempts,
                'difficulty' => $row->difficulty,
                'featured' => (bool) $row->featured,
            ],
            'mockSections' => [
                'id' => $row->slug,
                'label' => $row->label,
                'color' => $row->color,
                'soft' => $row->soft,
                'band' => $row->band,
            ],
            'certificates' => [
                'id' => $row->slug,
                'title' => $row->title,
                'subtitle' => $row->subtitle,
                'examId' => $row->exam_slug,
                'category' => $row->category,
                'coverImage' => $row->cover_image ?? '',
                'badgeImage' => $row->badge_image ?? '',
                'recipient' => $row->meta['recipient'] ?? '',
                'scoreLabel' => $row->score_label,
                'scoreValue' => $row->score_value,
                'issuedOn' => $row->issued_on,
                'credentialId' => $row->meta['credential_id'] ?? '',
                'hoursCompleted' => $row->meta['hours_completed'] ?? 0,
                'modules' => $row->meta['modules'] ?? [],
                'status' => $row->status,
                'level' => $row->meta['level'] ?? 'Proficient',
            ],
            'certCategories' => [
                'id' => $row->slug,
                'label' => $row->label,
                'hint' => $row->hint,
            ],
            'pteQuestions' => QuestionEntry::expand(self::pteToForm($row), (string) $row->type),
            'pteTaskTypes' => [
                'id' => $row->slug,
                'label' => $row->label,
                'short' => $row->short,
                'color' => $row->color,
            ],
            'diagnostics' => [
                'id' => $row->external_id,
                'examId' => $row->exam_slug,
                'prompt' => $row->prompt,
                'options' => $row->options ?? [],
            ],
            'readingPassage' => [
                'id' => (string) $row->id,
                'label' => $row->label,
                'text' => $row->text,
            ],
            'readingQuestions' => (static function ($row) {
                $form = QuestionEntry::expand([
                    'id' => $row->number,
                    'prompt' => $row->prompt,
                    'part' => $row->part,
                    'options' => $row->options ?? [],
                    'correctIndex' => $row->correct_index,
                    'skillTag' => $row->skill_tag,
                    'tip' => $row->tip,
                ], 'rc-mcq');
                $form['options'] = $form['mcqOptions'];

                return $form;
            })($row),
            'media' => [
                'id' => $row->external_id,
                'url' => $row->url,
                'name' => $row->name,
                'alt' => $row->alt,
                'size' => $row->size,
                'mime' => $row->mime,
            ],
            default => [],
        };
    }

    private static function pteToForm(PteQuestion $row): array
    {
        $p = $row->payload ?? [];

        return [
            'id' => $row->external_id,
            'title' => $row->title,
            'type' => $row->type,
            'section' => $row->section,
            'instructions' => $row->instructions,
            'text' => $p['text'] ?? '',
            'prompt' => $p['prompt'] ?? '',
            'audioText' => $p['audio_text'] ?? $p['audioText'] ?? '',
            'imageTitle' => $p['image_title'] ?? $p['imageTitle'] ?? '',
            'imageHint' => $p['image_hint'] ?? $p['imageHint'] ?? '',
            'imageUrl' => $row->image_url ?: ($p['image_url'] ?? $p['imageUrl'] ?? ''),
            'prepSeconds' => $p['prep_seconds'] ?? $p['prepSeconds'] ?? null,
            'recordSeconds' => $p['record_seconds'] ?? $p['recordSeconds'] ?? null,
            'timeLimitSeconds' => $p['time_limit_seconds'] ?? $p['timeLimitSeconds'] ?? null,
            'options' => $p['options'] ?? [],
            'multi' => (bool) ($p['multi'] ?? false),
            'correctText' => $row->correct_text,
            'blanks' => $p['blanks'] ?? [],
            'paragraphs' => $p['paragraphs'] ?? [],
            'wordBank' => $p['word_bank'] ?? $p['wordBank'] ?? [],
            'passageTokens' => $p['passage_tokens'] ?? $p['passageTokens'] ?? [],
            'transcript' => $p['transcript_with_errors'] ?? $p['transcript'] ?? [],
            'correctIndex' => $row->answer_key['index'] ?? '',
            'correctIndexes' => $row->answer_key['indexes'] ?? [],
            'answerKey' => $row->answer_key ?? [],
        ];
    }

    private static function saveExam($model, array $data): Exam
    {
        $model ??= new Exam;
        $model->fill([
            'slug' => $data['id'],
            'name' => $data['name'] ?? '',
            'short_name' => $data['shortName'] ?? '',
            'tagline' => $data['tagline'] ?? '',
            'accent' => $data['accent'] ?? '#2563eb',
            'accent_soft' => $data['accentSoft'] ?? Colors::softFromHex($data['accent'] ?? '#2563eb'),
            'description' => $data['description'] ?? '',
            'question_count' => (int) ($data['questionCount'] ?? 0),
            'free_practice' => (bool) ($data['freePractice'] ?? true),
            'cover_image' => $data['coverImage'] ?? '',
            'icon' => $data['icon'] ?? 'GraduationCap',
        ]);
        $model->save();

        return $model;
    }

    private static function saveCourse($model, array $data): Course
    {
        $model ??= new Course;
        $model->fill([
            'slug' => $data['id'],
            'name' => $data['name'] ?? '',
            'tag' => $data['tag'] ?? '',
            'color' => $data['color'] ?? '#2563eb',
            'soft' => $data['soft'] ?? Colors::softFromHex($data['color'] ?? '#2563eb'),
            'glow' => $data['glow'] ?? Colors::glowFromHex($data['color'] ?? '#2563eb'),
            'total' => $data['total'] ?? '',
            'cover_image' => $data['coverImage'] ?? '',
        ]);
        $model->save();
        $model->modules()->delete();
        foreach (array_values($data['modules'] ?? []) as $i => $mod) {
            CourseModule::query()->create([
                'course_id' => $model->id,
                'name' => $mod['name'] ?? 'Module',
                'count_label' => $mod['count'] ?? '',
                'value' => (int) ($mod['value'] ?? 0),
                'icon' => $mod['icon'] ?? 'BookOpen',
                'sort_order' => $i,
            ]);
        }

        return $model->load('modules');
    }

    private static function saveSkill($model, array $data): Skill
    {
        $model ??= new Skill;
        $model->fill([
            'slug' => $data['id'],
            'label' => $data['label'] ?? '',
            'color' => $data['color'] ?? '#2563eb',
            'soft' => $data['soft'] ?? Colors::softFromHex($data['color'] ?? '#2563eb'),
            'icon' => $data['icon'] ?? 'Sparkles',
        ]);
        $model->save();

        return $model;
    }

    private static function saveTask($model, array $data): TaskType
    {
        $model ??= new TaskType;
        $model->fill([
            'slug' => $data['id'],
            'name' => $data['name'] ?? '',
            'skill' => $data['skill'] ?? 'speaking',
            'exam_slugs' => array_values($data['examIds'] ?? []),
            'done' => (int) ($data['done'] ?? 0),
            'total' => (int) ($data['total'] ?? 0),
        ]);
        $model->save();

        return $model;
    }

    private static function saveQuestion($model, array $data): PracticeQuestion
    {
        $task = TaskType::query()->where('slug', $data['taskId'] ?? '')->first();
        $model ??= new PracticeQuestion;
        $slug = $data['taskId'] ?? $task?->slug ?? 'read-aloud';
        $compiled = QuestionEntry::compile($data, $slug);
        $model->fill([
            'external_id' => $data['id'],
            'task_type_id' => $task?->id,
            'skill' => $task?->skill,
            'task_slug' => $slug,
            'title' => $data['title'] ?? '',
            'difficulty' => $data['difficulty'] ?? 'Medium',
            'appeared' => (int) ($data['appeared'] ?? 0),
            'is_prediction' => (bool) ($data['isPrediction'] ?? false),
            'practiced' => (bool) ($data['practiced'] ?? false),
            'bookmarked' => (bool) ($data['bookmarked'] ?? false),
            'prompt' => $data['prompt'] ?? $model->prompt,
            'passage' => $compiled['passage'] ?? ($data['passage'] ?? $model->passage),
            'image_url' => $data['imageUrl'] ?? '',
            'exam_slugs' => array_values($data['examIds'] ?? []),
            'meta' => PracticePayload::mergeAdmin($model->meta ?? [], array_merge($data, $compiled), $slug),
        ]);
        $model->save();

        return $model;
    }

    private static function saveMock($model, array $data): MockTest
    {
        $model ??= new MockTest;
        $slug = $data['id'];
        $exam = $data['examId'] ?? 'ielts';
        $runner = $model->runner ?? 'generic';
        if (! $model->exists) {
            $runner = str_starts_with($slug, 'pte') ? 'pte' : (str_contains($slug, '-r-') ? 'ielts-reading' : 'generic');
        }
        $model->fill([
            'slug' => $slug,
            'title' => $data['title'] ?? '',
            'subtitle' => $data['subtitle'] ?? '',
            'exam_slug' => $exam,
            'section' => $data['section'] ?? 'full',
            'cover_image' => $data['coverImage'] ?? '',
            'duration_min' => (int) ($data['durationMin'] ?? 60),
            'questions' => (int) ($data['questions'] ?? 0),
            'attempts' => (int) ($data['attempts'] ?? 0),
            'difficulty' => $data['difficulty'] ?? 'Standard',
            'featured' => (bool) ($data['featured'] ?? false),
            'runner' => $runner,
        ]);
        $model->save();

        return $model;
    }

    private static function saveMockSection($model, array $data): MockSection
    {
        $model ??= new MockSection;
        $model->fill([
            'slug' => $data['id'],
            'label' => $data['label'] ?? '',
            'color' => $data['color'] ?? '#2563eb',
            'soft' => $data['soft'] ?? Colors::softFromHex($data['color'] ?? '#2563eb'),
            'band' => $data['band'] ?? '',
        ]);
        $model->save();

        return $model;
    }

    private static function saveCertificate($model, array $data): Certificate
    {
        $model ??= new Certificate;
        $model->fill([
            'slug' => $data['id'],
            'title' => $data['title'] ?? '',
            'subtitle' => $data['subtitle'] ?? '',
            'exam_slug' => $data['examId'] ?? 'pte',
            'category' => $data['category'] ?? 'module',
            'cover_image' => $data['coverImage'] ?? '',
            'badge_image' => $data['badgeImage'] ?? '',
            'score_label' => $data['scoreLabel'] ?? '',
            'score_value' => $data['scoreValue'] ?? '',
            'issued_on' => $data['issuedOn'] ?? '',
            'status' => $data['status'] ?? 'earned',
            'meta' => [
                'recipient' => $data['recipient'] ?? '',
                'credential_id' => $data['credentialId'] ?? '',
                'hours_completed' => (int) ($data['hoursCompleted'] ?? 0),
                'modules' => array_values($data['modules'] ?? []),
                'level' => $data['level'] ?? 'Proficient',
            ],
        ]);
        $model->save();

        return $model;
    }

    private static function saveCertCategory($model, array $data): CertificateCategory
    {
        $model ??= new CertificateCategory;
        $model->fill([
            'slug' => $data['id'],
            'label' => $data['label'] ?? '',
            'hint' => $data['hint'] ?? '',
        ]);
        $model->save();

        return $model;
    }

    private static function savePte($model, array $data): PteQuestion
    {
        $model ??= new PteQuestion;
        $compiled = QuestionEntry::compile($data, (string) ($data['type'] ?? 'read-aloud'));
        $payload = $model->payload ?? [];
        foreach (['blanks', 'paragraphs', 'word_bank', 'wordBank', 'passage_tokens', 'passageTokens', 'transcript_with_errors', 'transcript', 'options', 'multi'] as $forget) {
            unset($payload[$forget]);
        }
        $payload = array_merge($payload, array_filter([
            'text' => $compiled['text'] ?? ($data['text'] ?? null),
            'prompt' => $data['prompt'] ?? null,
            'audio_text' => $data['audioText'] ?? null,
            'image_title' => $data['imageTitle'] ?? null,
            'image_hint' => $data['imageHint'] ?? null,
            'image_url' => $data['imageUrl'] ?? null,
            'prep_seconds' => isset($data['prepSeconds']) && $data['prepSeconds'] !== '' ? (int) $data['prepSeconds'] : null,
            'record_seconds' => isset($data['recordSeconds']) && $data['recordSeconds'] !== '' ? (int) $data['recordSeconds'] : null,
            'time_limit_seconds' => isset($data['timeLimitSeconds']) && $data['timeLimitSeconds'] !== '' ? (int) $data['timeLimitSeconds'] : null,
            'options' => $compiled['options'] ?? ($data['options'] ?? null),
            'multi' => $compiled['multi'] ?? (array_key_exists('multi', $data) ? (bool) $data['multi'] : null),
            'blanks' => $compiled['blanks'] ?? null,
            'paragraphs' => $compiled['paragraphs'] ?? null,
            'word_bank' => $compiled['word_bank'] ?? null,
            'passage_tokens' => $compiled['passage_tokens'] ?? null,
            'transcript_with_errors' => $compiled['transcript_with_errors'] ?? null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []));

        if (array_key_exists('multi', $compiled)) {
            $payload['multi'] = (bool) $compiled['multi'];
        }

        $answerKey = $compiled['answer_key'] ?? null;
        if (! is_array($answerKey) || $answerKey === []) {
            if (! empty($data['correctText'])) {
                $answerKey = ['kind' => 'text', 'value' => $data['correctText']];
            } else {
                $answerKey = $model->answer_key;
            }
        }

        $model->fill([
            'external_id' => $data['id'],
            'title' => $data['title'] ?? '',
            'type' => $data['type'] ?? 'read-aloud',
            'section' => $data['section'] ?? 'speaking-writing',
            'instructions' => $data['instructions'] ?? '',
            'payload' => $payload,
            'image_url' => $data['imageUrl'] ?? '',
            'correct_text' => $compiled['correct_text'] ?? ($data['correctText'] ?? null),
            'answer_key' => $answerKey,
            'sort_order' => $model->sort_order ?? PteQuestion::query()->max('sort_order') + 1,
        ]);
        $model->save();

        return $model;
    }

    private static function savePteType($model, array $data): PteTaskType
    {
        $model ??= new PteTaskType;
        $model->fill([
            'slug' => $data['id'],
            'label' => $data['label'] ?? '',
            'short' => $data['short'] ?? '',
            'color' => $data['color'] ?? '#f06a4e',
        ]);
        $model->save();

        return $model;
    }

    private static function saveDiagnostic($model, array $data): DiagnosticQuestion
    {
        $model ??= new DiagnosticQuestion;
        $model->fill([
            'external_id' => $data['id'],
            'exam_slug' => $data['examId'] ?? 'pte',
            'prompt' => $data['prompt'] ?? '',
            'options' => is_array($data['options'] ?? null) ? array_values($data['options']) : [],
            'sort_order' => $model->sort_order ?? DiagnosticQuestion::query()->max('sort_order') + 1,
        ]);
        $model->save();

        return $model;
    }

    private static function savePassage($model, array $data): MockPassage
    {
        $model ??= new MockPassage;
        if (! $model->exists) {
            $model->mock_test_id = self::readingMockId();
            $model->sort_order = MockPassage::query()->where('mock_test_id', $model->mock_test_id)->max('sort_order') + 1;
        }
        $model->fill([
            'label' => $data['label'] ?? 'A',
            'text' => $data['text'] ?? '',
        ]);
        $model->save();

        return $model;
    }

    private static function saveReadingQuestion($model, array $data): MockQuestion
    {
        $model ??= new MockQuestion;
        if (! $model->exists) {
            $model->mock_test_id = self::readingMockId();
        }
        $options = [];
        $correct = 0;
        foreach (array_values($data['options'] ?? $data['mcqOptions'] ?? []) as $row) {
            if (is_string($row)) {
                $options[] = $row;
                continue;
            }
            $text = trim((string) ($row['text'] ?? $row['label'] ?? ''));
            if ($text === '') {
                continue;
            }
            if (! empty($row['correct'])) {
                $correct = count($options);
            }
            $options[] = $text;
        }
        $model->fill([
            'number' => (int) ($data['id'] ?? 1),
            'prompt' => $data['prompt'] ?? '',
            'part' => (int) ($data['part'] ?? 1),
            'options' => $options,
            'correct_index' => $correct,
            'skill_tag' => $data['skillTag'] ?? null,
            'tip' => $data['tip'] ?? null,
        ]);
        $model->save();

        return $model;
    }

    private static function readingMockId(): int
    {
        $mock = MockTest::query()->where('slug', 'ielts-r-rising-sum')->first()
            ?? MockTest::query()->where('exam_slug', 'ielts')->where('section', 'reading')->first();

        return $mock?->id ?? 0;
    }

    private static function readingPassages(): Collection
    {
        $id = self::readingMockId();

        return $id ? MockPassage::query()->where('mock_test_id', $id)->orderBy('sort_order')->get() : collect();
    }

    private static function readingQuestions(): Collection
    {
        $id = self::readingMockId();

        return $id ? MockQuestion::query()->where('mock_test_id', $id)->orderBy('number')->get() : collect();
    }

    private static function decodeJson(mixed $value): mixed
    {
        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') {
                return [];
            }
            $decoded = json_decode($trim, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }

    public static function storeUpload($file, string $alt = ''): MediaItem
    {
        $dir = public_path('uploads');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $original = $file->getClientOriginalName();
        $mime = method_exists($file, 'getMimeType') ? $file->getMimeType() : ($file->getClientMimeType() ?: 'application/octet-stream');
        $ext = $file->getClientOriginalExtension() ?: 'bin';
        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'upload';
        $name = $base.'-'.Str::lower(Str::random(6)).'.'.$ext;
        if (method_exists($file, 'storeAs')) {
            $file->storeAs('/', $name, 'uploads');
        } else {
            $file->move($dir, $name);
        }
        $url = '/uploads/'.$name;

        return MediaItem::query()->create([
            'external_id' => (string) Str::uuid(),
            'url' => $url,
            'name' => $original ?: $name,
            'alt' => $alt ?: $base,
            'size' => @filesize($dir.'/'.$name) ?: 0,
            'mime' => $mime ?: 'application/octet-stream',
        ]);
    }
}
