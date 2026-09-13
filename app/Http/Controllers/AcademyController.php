<?php

namespace App\Http\Controllers;

use App\Support\Cms\Site;
use App\Support\ExamContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademyController extends Controller
{
    public function index(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $site = Site::get();

        return view('academy.index', compact('exam', 'site'));
    }

    public function plan(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $plan = session('academy_plan');

        return view('academy.plan', compact('exam', 'plan'));
    }
}
