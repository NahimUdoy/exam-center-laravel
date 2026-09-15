<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Support\AptitudeBank;
use App\Support\StudyDesk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_the_ielts_study_desk(): void
    {
        $this->seedExam('ielts', 'IELTS');

        $this->get(route('study.show', ['exam' => 'ielts']))
            ->assertOk()
            ->assertSee('IELTS study desk')
            ->assertSee('14-day plan')
            ->assertSee('Fluency');
    }

    public function test_each_exam_desk_has_today_plan_and_phrases(): void
    {
        foreach (['ielts', 'pte', 'bba', 'mba'] as $slug) {
            $this->seedExam($slug, strtoupper($slug));
            $desk = StudyDesk::forExam(Exam::query()->where('slug', $slug)->first());
            $this->assertNotEmpty($desk['today']['title']);
            $this->assertGreaterThanOrEqual(4, count($desk['plan']));
            $this->assertGreaterThanOrEqual(4, count($desk['phrases']));
            $this->assertGreaterThanOrEqual(3, count($desk['drills']));
        }
    }

    public function test_aptitude_bank_covers_bba_and_mba(): void
    {
        $this->assertGreaterThanOrEqual(35, count(AptitudeBank::questions('bba')));
        $this->assertGreaterThanOrEqual(35, count(AptitudeBank::questions('mba')));
        $tags = collect(AptitudeBank::questions('mba'))->pluck('tag');
        $this->assertTrue($tags->contains('CR'));
        $this->assertTrue($tags->contains('Quant'));
    }

    private function seedExam(string $slug, string $name): void
    {
        Exam::query()->create([
            'slug' => $slug,
            'name' => $name,
            'short_name' => $name,
            'tagline' => 'Practice',
            'accent' => '#2563eb',
            'accent_soft' => '#dbeafe',
            'description' => 'Practice track.',
        ]);
    }
}
