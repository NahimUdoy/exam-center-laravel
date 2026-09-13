<?php

namespace App\Livewire\Admin;

use App\Models\MediaItem;
use App\Support\Cms\Collections;
use App\Support\Cms\Colors;
use App\Support\Cms\QuestionEntry;
use App\Support\Cms\Repository;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class CollectionEditor extends Component
{
    use WithFileUploads;

    public string $collection;

    public ?string $itemId = null;

    public bool $isNew = false;

    public array $form = [];

    public string $error = '';

    public bool $saved = false;

    public ?TemporaryUploadedFile $upload = null;

    public string $uploadField = '';

    public bool $showLibrary = false;

    public string $libraryField = '';

    public function mount(string $collection, ?string $id = null): void
    {
        $config = Collections::get($collection);
        if (! $config) {
            abort(404);
        }
        $this->collection = $collection;
        if ($id === 'new' || $id === null) {
            $this->isNew = true;
            $this->form = $config['defaults'];
        } else {
            $found = Repository::find($config['key'], $id);
            if (! $found) {
                abort(404);
            }
            $this->itemId = $id;
            $this->form = $found;
        }
        $this->normalizeJsonFields($config);
        $this->expandEntry($config);
    }

    public function updatedFormAccent($value): void
    {
        if (is_string($value) && $value !== '') {
            $this->form['accentSoft'] = Colors::softFromHex($value);
        }
    }

    public function updatedFormColor($value): void
    {
        if (is_string($value) && $value !== '') {
            $this->form['soft'] = Colors::softFromHex($value);
            if (array_key_exists('glow', $this->form)) {
                $this->form['glow'] = Colors::glowFromHex($value);
            }
        }
    }

    public function updatedUpload(): void
    {
        if ($this->upload && $this->uploadField) {
            $this->storeUpload($this->uploadField);
        }
    }

    public function startUpload(string $field): void
    {
        $this->uploadField = $field;
    }

    public function storeUpload(string $field): void
    {
        $this->validate([
            'upload' => 'required|file|max:8192|mimes:jpg,jpeg,png,webp,gif,svg',
        ]);
        $media = Repository::storeUpload($this->upload, $this->form['title'] ?? $this->form['name'] ?? '');
        $this->form[$field] = $media->url;
        $this->upload = null;
        $this->saved = false;
    }

    public function clearImage(string $field): void
    {
        $this->form[$field] = '';
        $this->saved = false;
    }

    public function openLibrary(string $field): void
    {
        $this->libraryField = $field;
        $this->showLibrary = true;
    }

    public function pickMedia(string $url): void
    {
        if ($this->libraryField) {
            $this->form[$this->libraryField] = $url;
        }
        $this->showLibrary = false;
        $this->saved = false;
    }

    public function updatedFormTaskId($value): void
    {
        $this->form = QuestionEntry::expand($this->form, (string) $value);
    }

    public function updatedFormType($value): void
    {
        $this->form = QuestionEntry::expand($this->form, (string) $value);
    }

    public function addMcqOption(): void
    {
        $key = $this->mcqKey();
        $rows = array_values($this->form[$key] ?? []);
        $rows[] = ['text' => '', 'correct' => false];
        $this->form[$key] = $rows;
    }

    public function removeMcqOption(int $index): void
    {
        $key = $this->mcqKey();
        $rows = array_values($this->form[$key] ?? []);
        unset($rows[$index]);
        $this->form[$key] = array_values($rows);
    }

    public function setMcqCorrect(int $index): void
    {
        $key = $this->mcqKey();
        $slug = (string) ($this->form['type'] ?? $this->form['taskId'] ?? '');
        $rows = array_values($this->form[$key] ?? []);
        $multi = QuestionEntry::isMcqMulti($slug);
        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                $row = ['text' => (string) $row, 'correct' => false];
            }
            if ($multi) {
                if ($i === $index) {
                    $row['correct'] = ! ($row['correct'] ?? false);
                }
            } else {
                $row['correct'] = $i === $index;
            }
            $rows[$i] = $row;
        }
        $this->form[$key] = $rows;
    }

    public function addBlankRow(): void
    {
        $rows = array_values($this->form['blankRows'] ?? []);
        $rows[] = ['correct' => '', 'others' => ''];
        $this->form['blankRows'] = $rows;
    }

    public function removeBlankRow(int $index): void
    {
        $rows = array_values($this->form['blankRows'] ?? []);
        unset($rows[$index]);
        $this->form['blankRows'] = array_values($rows);
    }

    public function addWord(string $field): void
    {
        $rows = array_values($this->form[$field] ?? []);
        $rows[] = '';
        $this->form[$field] = $rows;
    }

    public function removeWord(string $field, int $index): void
    {
        $rows = array_values($this->form[$field] ?? []);
        unset($rows[$index]);
        $this->form[$field] = array_values($rows);
    }

    public function addParagraph(): void
    {
        $rows = array_values($this->form['paragraphRows'] ?? []);
        $rows[] = ['text' => ''];
        $this->form['paragraphRows'] = $rows;
    }

    public function removeParagraph(int $index): void
    {
        $rows = array_values($this->form['paragraphRows'] ?? []);
        unset($rows[$index]);
        $this->form['paragraphRows'] = array_values($rows);
    }

    public function moveParagraph(int $index, int $delta): void
    {
        $rows = array_values($this->form['paragraphRows'] ?? []);
        $swap = $index + $delta;
        if (! isset($rows[$index], $rows[$swap])) {
            return;
        }
        [$rows[$index], $rows[$swap]] = [$rows[$swap], $rows[$index]];
        $this->form['paragraphRows'] = $rows;
    }

    public function addScoredOption(): void
    {
        $rows = array_values($this->form['options'] ?? []);
        $rows[] = ['label' => '', 'score' => 0];
        $this->form['options'] = $rows;
    }

    public function removeScoredOption(int $index): void
    {
        $rows = array_values($this->form['options'] ?? []);
        unset($rows[$index]);
        $this->form['options'] = array_values($rows);
    }

    public function splitHiw(): void
    {
        $text = (string) ($this->form['hiwDraft'] ?? '');
        $words = QuestionEntry::splitWords($text);
        $this->form['hiwWords'] = array_map(fn ($word) => ['word' => $word, 'incorrect' => false], $words);
        $this->form['hiwDraft'] = '';
    }

    public function toggleHiw(int $index): void
    {
        $rows = array_values($this->form['hiwWords'] ?? []);
        if (! isset($rows[$index])) {
            return;
        }
        $rows[$index]['incorrect'] = ! ($rows[$index]['incorrect'] ?? false);
        $this->form['hiwWords'] = $rows;
    }

    public function syncBlankRows(): void
    {
        $this->form = QuestionEntry::expand($this->form, $this->entrySlug());
    }

    public function addModule(): void
    {
        $mods = $this->form['modules'] ?? [];
        $mods[] = ['name' => 'New module', 'count' => '10+ sets', 'value' => 10, 'icon' => 'BookOpen'];
        $this->form['modules'] = $mods;
    }

    public function removeModule(int $index): void
    {
        $mods = array_values($this->form['modules'] ?? []);
        unset($mods[$index]);
        $this->form['modules'] = array_values($mods);
    }

    public function setTags(string $field, string $raw): void
    {
        $this->form[$field] = array_values(array_filter(array_map('trim', preg_split('/[,\\n]+/', $raw) ?: [])));
        $this->saved = false;
    }

    public function save(): void
    {
        $this->error = '';
        $this->saved = false;
        $config = Collections::get($this->collection);
        $payload = $this->form;
        foreach ($config['fields'] as $field) {
            if (($field['type'] ?? '') === 'json' && is_string($payload[$field['key']] ?? null)) {
                $decoded = json_decode($payload[$field['key']], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $payload[$field['key']] = $decoded;
                }
            }
            if (($field['type'] ?? '') === 'tags' && is_string($payload[$field['key']] ?? null)) {
                $payload[$field['key']] = array_values(array_filter(array_map('trim', explode(',', $payload[$field['key']]))));
            }
            if (($field['type'] ?? '') === 'boolean') {
                $payload[$field['key']] = (bool) ($payload[$field['key']] ?? false);
            }
        }
        try {
            $saved = Repository::save($config['key'], $payload, $this->isNew);
            $this->form = $saved;
            $this->normalizeJsonFields($config);
            $this->expandEntry($config);
            $wasNew = $this->isNew;
            $this->isNew = false;
            $this->itemId = (string) ($saved['id'] ?? $this->itemId);
            $this->saved = true;
            if ($wasNew) {
                $this->redirect(route('admin.collection.edit', [
                    'collection' => $config['path'],
                    'id' => $this->itemId,
                ]), navigate: true);
            }
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function deleteItem(): void
    {
        $config = Collections::get($this->collection);
        if ($this->itemId) {
            Repository::delete($config['key'], $this->itemId);
        }
        $this->redirect(route('admin.collection', ['collection' => $config['path']]), navigate: true);
    }

    public function render()
    {
        $config = Collections::get($this->collection);
        $groups = [];
        foreach ($config['fields'] as $field) {
            if (! QuestionEntry::visible($field, $this->form)) {
                continue;
            }
            $group = $field['group'] ?? 'Details';
            $groups[$group][] = $field;
        }
        $options = [];
        foreach ($config['fields'] as $field) {
            if (! empty($field['optionsFrom'])) {
                $options[$field['optionsFrom']] = Repository::options($field['optionsFrom']);
            }
        }
        $liveHref = Repository::resolveHref($config['itemHref'] ?? $config['liveHref'] ?? null, $this->form);

        return view('livewire.admin.collection-editor', [
            'config' => $config,
            'groups' => $groups,
            'options' => $options,
            'liveHref' => $liveHref,
            'mediaItems' => $this->showLibrary ? MediaItem::query()->latest()->limit(60)->get() : collect(),
            'presets' => Colors::PRESETS,
            'icons' => Colors::ICONS,
            'mcqMulti' => QuestionEntry::isMcqMulti($this->entrySlug()),
        ]);
    }

    private function normalizeJsonFields(array $config): void
    {
        foreach ($config['fields'] as $field) {
            $key = $field['key'];
            if (($field['type'] ?? '') === 'json' && ! is_string($this->form[$key] ?? null)) {
                $this->form[$key] = json_encode($this->form[$key] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            if (($field['type'] ?? '') === 'tags' && is_array($this->form[$key] ?? null)) {
                $this->form[$key] = implode(', ', $this->form[$key]);
            }
        }
    }

    private function expandEntry(array $config): void
    {
        $key = $config['key'] ?? '';
        if ($key === 'diagnostics') {
            $opts = $this->form['options'] ?? [];
            if (is_string($opts)) {
                $opts = json_decode($opts, true) ?: [];
            }
            $this->form['options'] = array_values(array_map(function ($row) {
                if (is_string($row)) {
                    return ['label' => $row, 'score' => 0];
                }

                return ['label' => (string) ($row['label'] ?? ''), 'score' => (int) ($row['score'] ?? 0)];
            }, is_array($opts) ? $opts : []));

            return;
        }
        if (! in_array($key, ['questions', 'pteQuestions', 'readingQuestions'], true)) {
            return;
        }
        $this->form = QuestionEntry::expand($this->form, $this->entrySlug());
        if ($key === 'readingQuestions') {
            $this->form['options'] = $this->form['mcqOptions'] ?? $this->form['options'] ?? [];
        }
    }

    private function entrySlug(): string
    {
        $config = Collections::get($this->collection);
        $key = $config['key'] ?? '';
        if ($key === 'pteQuestions') {
            return (string) ($this->form['type'] ?? '');
        }
        if ($key === 'readingQuestions') {
            return 'rc-mcq';
        }

        return (string) ($this->form['taskId'] ?? '');
    }

    private function mcqKey(): string
    {
        $config = Collections::get($this->collection);

        return ($config['key'] ?? '') === 'readingQuestions' ? 'options' : 'mcqOptions';
    }
}
