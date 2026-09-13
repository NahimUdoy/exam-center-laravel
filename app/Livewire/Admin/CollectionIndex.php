<?php

namespace App\Livewire\Admin;

use App\Support\Cms\Collections;
use App\Support\Cms\Repository;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CollectionIndex extends Component
{
    public string $collection;

    public string $q = '';

    public function mount(string $collection): void
    {
        $this->collection = $collection;
        if (! Collections::get($collection)) {
            abort(404);
        }
    }

    public function deleteItem(string $id): void
    {
        $config = Collections::get($this->collection);
        Repository::delete($config['key'], $id);
    }

    public function render()
    {
        $config = Collections::get($this->collection);
        $items = Repository::list($config['key']);
        if ($this->q !== '') {
            $q = mb_strtolower($this->q);
            $items = $items->filter(function ($item) use ($q) {
                return str_contains(mb_strtolower(json_encode($item)), $q);
            })->values();
        }

        return view('livewire.admin.collection-index', [
            'config' => $config,
            'items' => $items,
        ]);
    }
}
