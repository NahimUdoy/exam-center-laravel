<?php

namespace App\Http\Controllers;

use App\Support\ExamContext;
use App\Support\StudyDesk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudyController extends Controller
{
    public function show(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $desk = StudyDesk::forExam($exam);

        return view('study.show', compact('exam', 'desk'))
            ->with('variant', 'app');
    }
}
