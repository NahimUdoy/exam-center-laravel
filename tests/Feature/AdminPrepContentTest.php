<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPrepContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_overview_explains_how_to_add_prep_content(): void
    {
        $this->withSession(['nvs_admin' => true])
            ->get(route('admin.overview'))
            ->assertOk()
            ->assertSee('Add prep content')
            ->assertSee('Add exam')
            ->assertSee('Add question');
    }

    public function test_new_reading_task_uses_mcq_mode(): void
    {
        $this->assertSame('mcq', \App\Support\PracticeKind::mode('gre-quant', 'reading'));
        $this->assertSame('write', \App\Support\PracticeKind::mode('toefl-essay', 'writing'));
        $this->assertSame('speak-prompt', \App\Support\PracticeKind::mode('toefl-speak', 'speaking'));
    }
}
