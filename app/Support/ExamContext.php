<?php

namespace App\Support;

use App\Models\Exam;
use Illuminate\Support\Facades\Session;

class ExamContext
{
    public static function current(?string $fromQuery = null): Exam
    {
        if ($fromQuery) {
            Session::put('exam_slug', $fromQuery);
        }

        $slug = Session::get('exam_slug', 'ielts');
        $exam = Exam::query()->where('slug', $slug)->first();

        return $exam ?: Exam::query()->firstOrFail();
    }

    public static function set(string $slug): void
    {
        Session::put('exam_slug', $slug);
    }
}
