<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateCategory;
use App\Support\ExamContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(Request $request): View
    {
        $exam = ExamContext::current($request->query('exam'));
        $categories = CertificateCategory::query()->get()->keyBy('slug');
        $items = Certificate::query()
            ->where('exam_slug', $exam->slug)
            ->orderBy('category')
            ->orderBy('title')
            ->get();
        $certificates = $items->groupBy('category');
        $counts = [
            'all' => $items->count(),
            'earned' => $items->where('status', 'earned')->count(),
            'pending' => $items->where('status', 'pending')->count(),
            'locked' => $items->where('status', 'locked')->count(),
            'pathway' => $items->where('category', 'pathway')->count(),
            'module' => $items->where('category', 'module')->count(),
            'mock' => $items->where('category', 'mock')->count(),
        ];

        return view('certificates.index', compact('exam', 'certificates', 'categories', 'counts', 'items'))->with('variant', 'app');
    }

    public function show(Request $request, Certificate $certificate): View
    {
        $exam = ExamContext::current($request->query('exam', $certificate->exam_slug));

        return view('certificates.show', compact('exam', 'certificate'))->with('variant', 'app');
    }
}
