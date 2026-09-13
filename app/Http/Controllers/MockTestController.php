<?php

namespace App\Http\Controllers;

use App\Models\MockSection;
use App\Models\MockTest;
use App\Support\ExamContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MockTestController extends Controller
{
    public function index(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $sections = MockSection::query()->orderBy('id')->get()->keyBy('slug');
        $mocks = MockTest::query()
            ->where('exam_slug', $exam->slug)
            ->orderByDesc('featured')
            ->orderBy('title')
            ->get();

        $counts = [
            'all' => $mocks->count(),
            'speaking' => $mocks->where('section', 'speaking')->count(),
            'reading' => $mocks->where('section', 'reading')->count(),
            'listening' => $mocks->where('section', 'listening')->count(),
            'writing' => $mocks->where('section', 'writing')->count(),
            'full' => $mocks->where('section', 'full')->count(),
        ];

        return view('mock-tests.index', compact('exam', 'mocks', 'sections', 'counts'))->with('variant', 'app');
    }

    public function show(Request $request, MockTest $mock): View
    {
        $exam = ExamContext::current($request->query('exam', $mock->exam_slug));
        ExamContext::set($mock->exam_slug);

        return view('mock-tests.show', compact('exam', 'mock'))->with('variant', 'app');
    }
}
