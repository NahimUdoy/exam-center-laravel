<?php

namespace App\Livewire\Admin;

use App\Support\Cms\Colors;
use App\Support\Cms\Site;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use App\Support\Cms\Repository;
use App\Models\MediaItem;

#[Layout('layouts.admin')]
class SiteEditor extends Component
{
    use WithFileUploads;

    public array $site = [];

    public string $error = '';

    public bool $saved = false;

    public ?TemporaryUploadedFile $upload = null;

    public string $uploadField = '';

    public bool $showLibrary = false;

    public string $libraryField = '';

    public string $tickerText = '';

    public string $freeText = '';

    public function mount(): void
    {
        $this->site = Site::get();
        $this->tickerText = implode("\n", $this->site['ticker'] ?? []);
        $this->freeText = implode("\n", $this->site['freeItems'] ?? []);
    }

    public function updatedUpload(): void
    {
        if ($this->upload && $this->uploadField) {
            $media = Repository::storeUpload($this->upload, $this->uploadField);
            $this->site[$this->uploadField] = $media->url;
            $this->upload = null;
            $this->saved = false;
        }
    }

    public function openLibrary(string $field): void
    {
        $this->libraryField = $field;
        $this->showLibrary = true;
    }

    public function pickMedia(string $url): void
    {
        $this->site[$this->libraryField] = $url;
        $this->showLibrary = false;
    }

    public function addHowStep(): void
    {
        $this->site['howSteps'][] = [
            'id' => 'step-'.count($this->site['howSteps'] ?? []),
            'title' => 'New step',
            'body' => '',
            'color' => '#2563eb',
            'soft' => Colors::softFromHex('#2563eb'),
        ];
    }

    public function addPillar(): void
    {
        $this->site['pillars'][] = [
            'id' => 'pillar-'.count($this->site['pillars'] ?? []),
            'icon' => 'Sparkles',
            'title' => 'New pillar',
            'body' => '',
        ];
    }

    public function addNewsItem(): void
    {
        $this->site['newsModal']['items'][] = [
            'title' => 'New update',
            'body' => '',
        ];
    }

    public function save(): void
    {
        $this->error = '';
        $payload = $this->site;
        $payload['ticker'] = array_values(array_filter(array_map('trim', explode("\n", $this->tickerText))));
        $payload['freeItems'] = array_values(array_filter(array_map('trim', explode("\n", $this->freeText))));
        try {
            $this->site = Site::put($payload);
            $this->saved = true;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.admin.site-editor', [
            'presets' => Colors::PRESETS,
            'icons' => Colors::ICONS,
            'mediaItems' => $this->showLibrary ? MediaItem::query()->latest()->limit(60)->get() : collect(),
        ]);
    }
}
