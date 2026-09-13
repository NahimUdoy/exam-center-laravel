<?php

namespace App\Livewire\Admin;

use App\Support\Cms\Collections;
use App\Support\Cms\Repository;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Overview extends Component
{
    public function render()
    {
        return view('livewire.admin.overview', [
            'collections' => Collections::definitions(),
            'counts' => Repository::counts(),
        ]);
    }
}
