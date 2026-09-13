<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Exam;
use App\Models\MockTest;
use App\Support\Cms\Site;
use App\Support\ExamContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $exams = Exam::query()->orderBy('id')->get();
        $courses = Course::query()->with('modules')->orderBy('sort_order')->get();
        $site = Site::get();
        $mockCounts = MockTest::query()
            ->selectRaw('exam_slug, count(*) as total')
            ->groupBy('exam_slug')
            ->pluck('total', 'exam_slug');
        $totalMocks = MockTest::query()->count();
        $totalCertificates = Certificate::query()->count();

        return view('marketing.home', compact('exam', 'exams', 'courses', 'site', 'mockCounts', 'totalMocks', 'totalCertificates'));
    }
}
