<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\PracticeQuestion;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_task_type_but_not_see_question_titles(): void
    {
        [$task] = $this->seedPractice();

        $this->get(route('practice.task', ['skill' => 'speaking', 'task' => $task->slug, 'exam' => 'pte']))
            ->assertOk()
            ->assertSee('Read Aloud')
            ->assertSee('Log in to open')
            ->assertDontSee('Secret Passage Title');
    }

    public function test_guest_cannot_open_or_submit_a_question(): void
    {
        [$task, $question] = $this->seedPractice();

        $this->get(route('practice.question', [
            'skill' => 'speaking',
            'task' => $task->slug,
            'question' => $question->external_id,
            'exam' => 'pte',
        ]))->assertRedirect();

        $this->postJson(route('practice.submit', [
            'skill' => 'speaking',
            'task' => $task->slug,
            'question' => $question->external_id,
            'exam' => 'pte',
        ]))->assertUnauthorized();
    }

    public function test_authenticated_user_can_open_a_question(): void
    {
        [$task, $question] = $this->seedPractice();

        $this->actingAs(User::factory()->create())
            ->get(route('practice.question', [
                'skill' => 'speaking',
                'task' => $task->slug,
                'question' => $question->external_id,
                'exam' => 'pte',
            ]))
            ->assertOk()
            ->assertSee('Secret Passage Title');
    }

    /**
     * @return array{0: TaskType, 1: PracticeQuestion}
     */
    private function seedPractice(): array
    {
        Exam::query()->create([
            'slug' => 'pte',
            'name' => 'PTE Academic',
            'short_name' => 'PTE',
            'tagline' => 'Practice',
            'accent' => '#b4534a',
            'accent_soft' => '#fee2e2',
            'description' => 'Pearson test.',
        ]);

        $task = TaskType::query()->create([
            'slug' => 'read-aloud',
            'skill' => 'speaking',
            'name' => 'Read Aloud',
            'exam_slugs' => ['pte'],
        ]);

        $question = PracticeQuestion::query()->create([
            'external_id' => '9000001',
            'task_type_id' => $task->id,
            'skill' => 'speaking',
            'task_slug' => 'read-aloud',
            'title' => 'Secret Passage Title',
            'difficulty' => 'Medium',
            'prompt' => 'Read the text aloud.',
            'passage' => 'A hidden passage for the test.',
        ]);

        return [$task, $question];
    }
}
