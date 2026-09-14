<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\SpeakingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeakingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_one_to_one_speaking(): void
    {
        $this->seedExam();

        $this->get(route('speaking.show', ['exam' => 'ielts']))
            ->assertRedirect();
    }

    public function test_student_can_open_the_speaking_lobby(): void
    {
        $this->seedExam();

        $this->actingAs(User::factory()->create())
            ->get(route('speaking.show', ['exam' => 'ielts']))
            ->assertOk()
            ->assertSee('One-to-one speaking')
            ->assertSee('Start interview')
            ->assertSee('warm-up questions')
            ->assertSee('cue cards');
    }

    public function test_speaking_script_draws_a_full_interview_from_the_large_bank(): void
    {
        $this->seedExam();

        $this->assertGreaterThanOrEqual(200, count(\App\Support\SpeakingBank::interviews('ielts')));
        $this->assertGreaterThanOrEqual(70, count(\App\Support\SpeakingBank::cueCards('ielts')));

        $script = \App\Support\SpeakingScript::forExam(\App\Models\Exam::query()->first());
        $part1 = collect($script['steps'])->where('phase', 'part1');
        $part3 = collect($script['steps'])->where('phase', 'part3');
        $this->assertCount(5, $part1);
        $this->assertCount(3, $part3);
    }

    public function test_student_can_save_a_completed_session(): void
    {
        $this->seedExam();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('speaking.store', ['exam' => 'ielts']), [
                'seconds' => 95,
                'status' => 'completed',
                'turns' => [
                    ['role' => 'examiner', 'text' => 'Hello.', 'seconds' => 0, 'phase' => 'greet'],
                    ['role' => 'student', 'text' => 'I usually study alone in the library because it is quiet.', 'seconds' => 22, 'phase' => 'part1'],
                    ['role' => 'student', 'text' => 'A public place that helps me is the city library.', 'seconds' => 40, 'phase' => 'part2'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['score' => ['overall', 'traits', 'band', 'tips']]);

        $this->assertSame(1, SpeakingSession::query()->where('user_id', $user->id)->count());
    }

    private function seedExam(): void
    {
        Exam::query()->create([
            'slug' => 'ielts',
            'name' => 'IELTS Academic',
            'short_name' => 'IELTS',
            'tagline' => 'Practice',
            'accent' => '#2563eb',
            'accent_soft' => '#dbeafe',
            'description' => 'IELTS practice.',
        ]);
    }
}
