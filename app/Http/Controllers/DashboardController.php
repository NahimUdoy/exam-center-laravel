<?php

namespace App\Http\Controllers;

use App\Models\MockTest;
use App\Models\PracticeQuestion;
use App\Models\Skill;
use App\Models\TaskType;
use App\Support\ExamContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $skills = Skill::query()->orderBy('sort_order')->get();
        $tasks = TaskType::query()
            ->forExam($exam->slug)
            ->orderBy('skill')
            ->orderBy('name')
            ->get();

        $this->applyLivePracticeCounts($tasks);
        $this->applyLiveMockCounts($tasks, $exam->slug);

        $summary = $this->summaryCards($tasks, $skills);
        $tasks = $tasks->groupBy('skill');

        return view('dashboard.index', compact('exam', 'tasks', 'skills', 'summary'))
            ->with('variant', 'app');
    }

    private function applyLivePracticeCounts(Collection $tasks): void
    {
        $slugs = $tasks->where('skill', '!=', 'mock')->pluck('slug');
        if ($slugs->isEmpty()) {
            return;
        }

        $live = PracticeQuestion::query()
            ->whereIn('task_slug', $slugs)
            ->selectRaw('task_slug,
                count(*) as live_total,
                coalesce(sum(practiced), 0) as live_done,
                coalesce(sum(is_prediction), 0) as live_pred,
                coalesce(sum(case when is_prediction = 1 and practiced = 1 then 1 else 0 end), 0) as live_pred_done')
            ->groupBy('task_slug')
            ->get()
            ->keyBy('task_slug');

        foreach ($tasks as $task) {
            if ($task->skill === 'mock') {
                continue;
            }

            $row = $live->get($task->slug);
            $task->total = (int) ($row->live_total ?? 0);
            $task->done = (int) ($row->live_done ?? 0);
            $task->prediction_count = (int) ($row->live_pred ?? 0);
            $task->prediction_done = (int) ($row->live_pred_done ?? 0);
        }
    }

    private function applyLiveMockCounts(Collection $tasks, string $examSlug): void
    {
        $full = MockTest::query()->where('exam_slug', $examSlug)->where('section', 'full')->count();
        $sectional = MockTest::query()->where('exam_slug', $examSlug)->where('section', '!=', 'full')->count();

        foreach ($tasks as $task) {
            if ($task->slug === 'full-mock') {
                $task->total = $full;
            } elseif ($task->slug === 'sectional-mock') {
                $task->total = $sectional;
            }
        }
    }

    private function summaryCards(Collection $tasks, Collection $skills): array
    {
        $tone = fn (string $slug, string $fallback) => $skills->firstWhere('slug', $slug)?->color ?? $fallback;
        $practice = $tasks->where('skill', '!=', 'mock');

        $cards = [[
            'key' => 'prediction',
            'label' => 'Prediction',
            'count' => (int) $practice->sum('prediction_count'),
            'done' => (int) $practice->sum('prediction_done'),
            'tone' => '#f43f5e',
        ]];

        foreach (['speaking', 'writing', 'reading', 'listening'] as $skill) {
            $items = $practice->where('skill', $skill);
            $cards[] = [
                'key' => $skill,
                'label' => ucfirst($skill),
                'count' => (int) $items->sum('total'),
                'done' => (int) $items->sum('done'),
                'tone' => $tone($skill, '#64748b'),
            ];
        }

        return array_map(function (array $card) {
            $card['percent'] = $card['count'] > 0
                ? (int) round(($card['done'] / $card['count']) * 100)
                : 0;

            return $card;
        }, $cards);
    }
}
