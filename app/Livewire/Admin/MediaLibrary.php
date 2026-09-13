<?php

namespace App\Livewire\Admin;

use App\Models\MediaItem;
use App\Support\Cms\Repository;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class MediaLibrary extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $upload = null;

    public string $error = '';

    public function updatedUpload(): void
    {
        $this->validate(['upload' => 'required|file|max:8192|mimes:jpg,jpeg,png,webp,gif,svg']);
        try {
            Repository::storeUpload($this->upload);
            $this->upload = null;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function deleteItem(int $id): void
    {
        MediaItem::query()->whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.media-library', [
            'items' => MediaItem::query()->latest()->get(),
        ]);
    }
}
