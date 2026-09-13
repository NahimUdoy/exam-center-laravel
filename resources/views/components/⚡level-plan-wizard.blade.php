<?php

use App\Models\AcademyPlan;
use App\Models\DiagnosticQuestion;
use App\Models\Exam;
use App\Services\PlanBuilder;
use App\Support\ExamContext;
use Livewire\Component;

new class extends Component
{
    public string $step = 'welcome';
    public string $examSlug = 'ielts';
    public string $feeling = 'building';
    public int $quizIndex = 0;
    public array $scores = [];
    public int $days = 5;

    public function selectExam(string $slug): void
    {
        $this->examSlug = $slug;
    }

    public function nextFromExam(): void
    {
        $this->step = 'feeling';
    }

    public function nextFromFeeling(): void
    {
        $this->quizIndex = 0;
        $this->scores = [];
        $this->step = 'quiz';
    }

    public function answer(int $score): void
    {
        $this->scores[] = $score;
        $questions = DiagnosticQuestion::query()
            ->where('exam_slug', $this->examSlug)
            ->orderBy('sort_order')
            ->get();

        if ($this->quizIndex + 1 < $questions->count()) {
            $this->quizIndex++;
        } else {
            $this->step = 'time';
        }
    }

    public function finish(): void
    {
        if (! auth()->check()) {
            $this->js('window.dispatchEvent(new CustomEvent("nvs-auth-required"))');

            return;
        }

        $plan = PlanBuilder::build($this->examSlug, $this->feeling, $this->scores, $this->days);
        ExamContext::set($this->examSlug);

        AcademyPlan::query()->create([
            'user_id' => auth()->id(),
            'exam_slug' => $this->examSlug,
            'tier' => $plan['tier'],
            'level_label' => $plan['level_label'],
            'plan_json' => $plan,
        ]);

        session(['academy_plan' => $plan]);
        $this->redirect(route('academy.plan'), navigate: true);
    }

    public function render()
    {
        $exams = Exam::query()->orderBy('id')->get();
        $questions = DiagnosticQuestion::query()
            ->where('exam_slug', $this->examSlug)
            ->orderBy('sort_order')
            ->get();

        $order = ['welcome', 'exam', 'feeling', 'quiz', 'time'];
        $progress = ((array_search($this->step, $order, true) ?: 0) + 1) / count($order) * 100;

        return $this->view([
            'exams' => $exams,
            'questions' => $questions,
            'progress' => $progress,
            'currentQuestion' => $questions->get($this->quizIndex),
        ]);
    }
};
?>

<div id="plan" class="overflow-hidden rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-white shadow-premium-lg">
    <div class="border-b border-[rgba(15,40,80,0.08)] bg-gradient-to-r from-brand-soft/80 to-white px-5 py-4 sm:px-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand">NextVerseSkill Academy · ~1 minute</p>
        <h3 class="font-display mt-1 text-xl font-bold text-ink sm:text-2xl">Tell Us Your Level</h3>
        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-fog">
            <div class="h-full rounded-full bg-gradient-to-r from-brand to-brand-deep transition-all" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <div class="px-5 py-6 sm:px-6 sm:py-8">
        @if($step === 'welcome')
            <div class="mx-auto max-w-xl text-center">
                <p class="text-base text-muted sm:text-lg">Answer a few friendly questions. Academy opens your full premium study plan page with weekly detail and total study time.</p>
                <button type="button" wire:click="$set('step','exam')" class="mt-7 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-brand to-brand-deep px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30">Click Here →</button>
                <p class="mt-3 text-sm font-semibold text-brand">It only takes a minute!</p>
            </div>
        @endif

        @if($step === 'exam')
            <p class="font-display text-lg font-bold text-ink">Which journey are we walking with you?</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($exams as $exam)
                    <button type="button" wire:click="selectExam('{{ $exam->slug }}')"
                        class="rounded-2xl border px-4 py-4 text-left {{ $examSlug === $exam->slug ? 'border-transparent text-white shadow-md' : 'border-[rgba(15,40,80,0.08)] bg-mist/50' }}"
                        @if($examSlug === $exam->slug) style="background: {{ $exam->accent }}" @endif>
                        <p class="font-display text-lg font-bold">{{ $exam->short_name }}</p>
                        <p class="mt-1 text-xs {{ $examSlug === $exam->slug ? 'text-white/80' : 'text-muted' }}">{{ $exam->tagline }}</p>
                    </button>
                @endforeach
            </div>
            <div class="mt-7 flex justify-between">
                <button type="button" wire:click="$set('step','welcome')" class="text-sm font-semibold text-muted">← Back</button>
                <button type="button" wire:click="nextFromExam" class="rounded-full bg-gradient-to-r from-brand to-brand-deep px-5 py-2.5 text-sm font-bold text-white">Continue →</button>
            </div>
        @endif

        @if($step === 'feeling')
            <p class="font-display text-lg font-bold text-ink">Be honest — where do you feel you are?</p>
            <div class="mt-5 space-y-3">
                @foreach([
                    'starting' => ['Just starting', 'Practice still feels new.'],
                    'building' => ['Building momentum', 'I need a clear path to continue.'],
                    'ready' => ['Almost exam-ready', 'I need polish and consistency.'],
                ] as $id => [$title, $body])
                    <button type="button" wire:click="$set('feeling','{{ $id }}')"
                        class="flex w-full items-start gap-3 rounded-2xl border px-4 py-4 text-left {{ $feeling === $id ? 'border-brand bg-brand-soft/60' : 'border-[rgba(15,40,80,0.08)]' }}">
                        <span class="font-semibold text-ink">{{ $title }}</span>
                        <span class="block w-full text-sm text-muted">{{ $body }}</span>
                    </button>
                @endforeach
            </div>
            <div class="mt-7 flex justify-between">
                <button type="button" wire:click="$set('step','exam')" class="text-sm font-semibold text-muted">← Back</button>
                <button type="button" wire:click="nextFromFeeling" class="rounded-full bg-gradient-to-r from-brand to-brand-deep px-5 py-2.5 text-sm font-bold text-white">Quick check →</button>
            </div>
        @endif

        @if($step === 'quiz' && $currentQuestion)
            <p class="text-xs font-bold uppercase tracking-wide text-brand">Quick check {{ $quizIndex + 1 }}/{{ $questions->count() }}</p>
            <p class="font-display mt-2 text-lg font-bold text-ink">{{ $currentQuestion->prompt }}</p>
            <div class="mt-5 space-y-3">
                @foreach($currentQuestion->options as $opt)
                    <button type="button" wire:click="answer({{ (int) ($opt['score'] ?? 0) }})"
                        class="w-full rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/40 px-4 py-3.5 text-left text-sm font-medium hover:border-brand/40 hover:bg-brand-soft/50">
                        {{ $opt['label'] ?? $opt['text'] ?? json_encode($opt) }}
                    </button>
                @endforeach
            </div>
        @endif

        @if($step === 'time')
            <p class="font-display text-lg font-bold text-ink">How many days each week can you show up?</p>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach([3,4,5,6,7] as $d)
                    <button type="button" wire:click="$set('days', {{ $d }})"
                        class="rounded-full px-5 py-2.5 text-sm font-bold {{ $days === $d ? 'bg-brand text-white' : 'bg-mist text-muted' }}">{{ $d }} days</button>
                @endforeach
            </div>
            <div class="mt-7 flex justify-between">
                <button type="button" wire:click="$set('step','feeling')" class="text-sm font-semibold text-muted">← Back</button>
                <button type="button" wire:click="finish" class="rounded-full bg-gradient-to-r from-brand to-brand-deep px-5 py-2.5 text-sm font-bold text-white">Open Academy plan →</button>
            </div>
        @endif
    </div>
</div>
