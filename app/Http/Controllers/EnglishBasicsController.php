<?php

namespace App\Http\Controllers;

use App\Models\EnglishLesson;
use App\Support\EnglishBasics\Catalog;
use App\Support\ExamContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnglishBasicsController extends Controller
{
    public function index(Request $request): View
    {
        $exam = ExamContext::current('english-basic');
        $lessons = EnglishLesson::query()->withCount('drills')->orderBy('sort_order')->get();
        $progress = session('english_basics', []);
        $completed = collect($progress)->count();
        $byLetter = $lessons->groupBy('letter');
        $categories = EnglishLesson::categoryMeta();
        $categoryOrder = array_keys($categories);
        $byCategory = $lessons->groupBy('category');

        $path = collect(Catalog::pathSlugs())
            ->map(fn (string $slug) => $lessons->firstWhere('slug', $slug))
            ->filter();

        $continue = $path->first(fn ($lesson) => ! isset($progress[$lesson->slug]))
            ?? $lessons->first(fn ($lesson) => ! isset($progress[$lesson->slug]))
            ?? $lessons->first();

        return view('english-basics.index', compact(
            'exam', 'lessons', 'progress', 'completed', 'byLetter', 'categories',
            'byCategory', 'categoryOrder', 'path', 'continue'
        ))->with('variant', 'app');
    }

    public function show(string $topic): View
    {
        $exam = ExamContext::current('english-basic');
        $lesson = EnglishLesson::query()->where('slug', $topic)->with('drills')->firstOrFail();
        $all = EnglishLesson::query()->orderBy('sort_order')->get(['id', 'slug', 'title', 'letter', 'category', 'subtitle', 'level']);
        $index = $all->search(fn ($row) => $row->id === $lesson->id);
        $prev = $index > 0 ? $all[$index - 1] : null;
        $next = $index !== false && $index < $all->count() - 1 ? $all[$index + 1] : null;
        $progress = session('english_basics', [])[$lesson->slug] ?? null;
        $categories = EnglishLesson::categoryMeta();
        $meta = $categories[$lesson->category] ?? ['label' => 'Lesson', 'color' => $exam->accent, 'soft' => $exam->accent_soft];
        $related = $lesson->related(4);

        $pathSlugs = Catalog::pathSlugs();
        $pathIndex = array_search($lesson->slug, $pathSlugs, true);
        $pathNextSlug = $pathIndex !== false ? ($pathSlugs[$pathIndex + 1] ?? null) : null;
        $pathNext = $pathNextSlug
            ? $all->firstWhere('slug', $pathNextSlug)
            : $next;

        return view('english-basics.show', compact(
            'exam', 'lesson', 'prev', 'next', 'progress', 'meta', 'all', 'related', 'pathNext', 'categories'
        ))->with('variant', 'app');
    }

    public function complete(Request $request, string $topic): JsonResponse
    {
        $lesson = EnglishLesson::query()->where('slug', $topic)->firstOrFail();
        $data = $request->validate([
            'correct' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1'],
        ]);

        $progress = session('english_basics', []);
        $progress[$lesson->slug] = [
            'correct' => (int) $data['correct'],
            'total' => (int) $data['total'],
            'completed_at' => now()->toIso8601String(),
        ];
        session(['english_basics' => $progress]);

        return response()->json(['ok' => true]);
    }
}
