<?php

namespace App\Http\Controllers;

use App\Models\SpeakingSession;
use App\Support\ExamContext;
use App\Support\SpeakingScript;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpeakingController extends Controller
{
    public function show(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $script = SpeakingScript::forExam($exam);

        return view('speaking.one-to-one', compact('exam', 'script'))->with('variant', 'app');
    }

    public function store(Request $request): JsonResponse
    {
        $exam = ExamContext::current($request->query('exam'));
        $data = $request->validate([
            'turns' => ['required', 'array', 'min:1'],
            'turns.*.role' => ['required', 'in:examiner,student'],
            'turns.*.text' => ['nullable', 'string', 'max:4000'],
            'turns.*.seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
            'turns.*.phase' => ['nullable', 'string', 'max:20'],
            'seconds' => ['required', 'integer', 'min:0', 'max:3600'],
            'status' => ['nullable', 'in:started,completed'],
        ]);

        $score = SpeakingScript::score($data['turns'], $data['seconds']);

        $session = SpeakingSession::query()->create([
            'user_id' => $request->user()->id,
            'exam_slug' => $exam->slug,
            'status' => $data['status'] ?? 'completed',
            'turns' => $data['turns'],
            'seconds' => $data['seconds'],
            'score' => $score,
        ]);

        return response()->json([
            'ok' => true,
            'id' => $session->id,
            'score' => $score,
        ]);
    }
}
