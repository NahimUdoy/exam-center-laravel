<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin-auth')]
#[Title('Admin — NextVerseSkill')]
class Login extends Component
{
    public string $password = '';

    public string $error = '';

    public function submit(): void
    {
        $this->error = '';
        $expected = (string) config('cms.password');
        if (! hash_equals($expected, $this->password)) {
            $this->error = 'That password doesn’t match.';

            return;
        }

        session(['nvs_admin' => true]);
        $this->redirect(route('admin.overview'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.login');
    }
}
