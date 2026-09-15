<?php

namespace App\Http\Controllers;

use App\Models\PracticeQuestion;
use App\Models\TaskType;
use App\Support\ExamContext;
use App\Support\PracticeBank;
use App\Support\PracticeKind;
use App\Support\PracticePayload;
use App\Support\PracticeScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PracticeController extends Controller
{
    public function task(Request $request, string $skill, string $task): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $taskType = TaskType::query()
            ->where('slug', $task)
            ->where('skill', $skill)
            ->forExam($exam->slug)
            ->firstOrFail();

        $questions = PracticeBank::forTask($taskType);

        return view('practice.task', compact('exam', 'taskType', 'questions', 'skill'))->with('variant', 'app');
    }

    public function question(Request $request, string $skill, string $task, string $question): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $taskType = TaskType::query()
            ->where('slug', $task)
            ->where('skill', $skill)
            ->forExam($exam->slug)
            ->firstOrFail();

        $questions = PracticeBank::forTask($taskType);
        $item = $questions->firstWhere('external_id', $question) ?? $questions->first();
        abort_unless($item, 404);

        $index = $questions->search(fn ($q) => $q->external_id === $item->external_id);
        $index = $index === false ? 0 : $index;
        $kind = PracticeKind::for($taskType->slug, $taskType->skill);
        $payload = PracticePayload::hydrate($item);

        return view('practice.question', compact('exam', 'taskType', 'item', 'skill', 'questions', 'index', 'kind', 'payload'))->with('variant', 'app');
    }

    public function submit(Request $request, string $skill, string $task, string $question): JsonResponse
    {
        $exam = ExamContext::current($request->query('exam'));
        $taskType = TaskType::query()
            ->where('slug', $task)
            ->where('skill', $skill)
            ->forExam($exam->slug)
            ->firstOrFail();

        $item = PracticeQuestion::query()
            ->where('task_slug', $task)
            ->where('external_id', $question)
            ->firstOrFail();

        $seconds = (int) $request->input('recorded_seconds', 0);
        $answer = $request->input('answer');
        $score = PracticeScorer::for($item, $seconds, $skill, $answer);
        $wasNew = ! $item->practiced;

        $item->practiced = true;
        $item->meta = array_merge($item->meta ?? [], [
            'last_score' => $score,
            'last_seconds' => $seconds,
            'last_answer' => $answer,
            'last_difficulty' => $request->input('difficulty'),
        ]);
        $item->save();

        if ($wasNew) {
            $taskType->increment('done');
        }

        return response()->json(['ok' => true, 'score' => $score]);
    }
}
