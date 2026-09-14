@php
    use App\Models\Exam;
    use App\Support\Cms\Site;
    use App\Support\ExamContext;
    use App\Support\Theme;
    $exams = Exam::query()->orderBy('id')->get();
    $current = ExamContext::current(request('exam'));
    $marketing = ($variant ?? 'marketing') === 'marketing';
    $site = $site ?? Site::get();
    $brand = $site['brandName'] ?? 'NextVerseSkill';
    $theme = $theme ?? Theme::resolve();
    $classic = $theme === Theme::CLASSIC;
    $pill = $classic ? 'rounded-full' : 'rounded-2xl';
@endphp
<header class="sticky top-0 z-50 border-b {{ $classic ? 'border-[rgba(15,40,80,0.08)]' : 'border-[rgba(49,36,24,0.08)]' }} {{ $marketing ? 'bg-paper/92' : 'bg-paper/95' }} backdrop-blur-xl">
    @unless($marketing)
        @if($classic)
            <div class="bg-[#071428]">
                <div class="mx-auto flex h-12 max-w-7xl items-center gap-3 px-4 sm:px-6">
                    <span class="hidden shrink-0 text-[10px] font-extrabold uppercase tracking-[0.2em] text-white/35 sm:inline">Exam</span>
                    <div class="flex min-w-0 flex-1 items-center gap-0.5 overflow-x-auto rounded-full bg-white/10 p-1 scrollbar-thin" role="tablist" aria-label="Choose exam">
                        @foreach($exams as $exam)
                            @php $active = $current->slug === $exam->slug; @endphp
                            <a href="{{ request()->fullUrlWithQuery(['exam' => $exam->slug]) }}"
                               class="exam-pill relative shrink-0 rounded-full px-3.5 py-1.5 text-sm font-bold outline-none transition-all duration-300 {{ $active ? 'bg-white text-ink shadow-[0_8px_22px_rgba(0,0,0,0.28)]' : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                                @if($active)
                                    <span class="pointer-events-none absolute inset-0 rounded-full" style="box-shadow: inset 0 0 0 1.5px {{ $exam->accent }}"></span>
                                @endif
                                <span class="mr-2 inline-block h-1.5 w-1.5 rounded-full" style="background: {{ $exam->accent }}; {{ $active ? 'box-shadow: 0 0 0 3px '.$exam->accent.'33' : '' }}"></span>
                                {{ $exam->short_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="border-b border-[rgba(49,36,24,0.06)] bg-mist/90">
                <div class="mx-auto flex h-12 max-w-7xl items-center gap-3 px-4 sm:px-6">
                    <span class="hidden shrink-0 text-[10px] font-extrabold uppercase tracking-[0.2em] text-muted sm:inline">Studio</span>
                    <div class="flex min-w-0 flex-1 items-center gap-1 overflow-x-auto p-1 scrollbar-thin" role="tablist" aria-label="Choose exam">
                        @foreach($exams as $exam)
                            @php $active = $current->slug === $exam->slug; @endphp
                            <a href="{{ request()->fullUrlWithQuery(['exam' => $exam->slug]) }}"
                               class="exam-pill relative shrink-0 rounded-2xl px-3.5 py-1.5 text-sm font-bold outline-none transition-all duration-300 {{ $active ? 'bg-paper text-ink shadow-deck' : 'text-muted hover:bg-paper/80 hover:text-ink' }}">
                                <span class="mr-2 inline-block h-2 w-2 rounded-full" style="background: {{ $exam->accent }}; {{ $active ? 'box-shadow: 0 0 0 3px '.$exam->accent.'33' : '' }}"></span>
                                {{ $exam->short_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endunless

    <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-4 px-4 sm:h-16 sm:px-6">
        <a href="{{ route('academy') }}" class="font-display inline-flex items-center gap-2.5 font-bold tracking-tight text-ink">
            @if(!empty($site['logoImage']))
                <img src="{{ $site['logoImage'] }}" alt="{{ $brand }}" class="h-9 w-9 {{ $pill }} object-cover">
            @else
                <span class="grid h-9 w-9 place-items-center {{ $pill }} bg-brand text-[11px] font-extrabold text-white shadow-lg shadow-brand/20">NV</span>
            @endif
            <span class="leading-tight">
                <span class="block text-[0.95rem] sm:text-[1.15rem]">NextVerse<span class="{{ $classic ? 'bg-gradient-to-r from-brand to-sky bg-clip-text text-transparent' : 'text-brand' }}">Skill</span></span>
                <span class="block text-[0.65rem] font-extrabold uppercase tracking-[0.16em] {{ $classic ? 'text-brand' : 'text-muted' }} sm:text-[0.72rem]">{{ $classic ? 'Academy' : 'Practice studio' }}</span>
            </span>
        </a>

        <nav class="hidden items-center gap-0.5 lg:flex">
            @php
                $basicsUrl = route('english-basics.index', ['exam' => 'english-basic']);
                $speakUrl = route('speaking.show', ['exam' => $current->slug]);
                $links = $marketing
                    ? [['/', 'Home'], ['/academy', 'Academy'], [$basicsUrl, 'Basics'], ['/dashboard', 'Practice'], [$speakUrl, 'Speak'], ['/mock-tests', 'Mock Tests'], ['/certificates', 'Certificates'], ['/#courses', 'All Courses']]
                    : [['/dashboard', 'Home'], ['/academy', 'Academy'], [$basicsUrl, 'Basics'], ['/dashboard', 'Practice'], [$speakUrl, 'Speak'], ['/mock-tests', 'Mock Tests'], ['/certificates', 'Certificates']];
            @endphp
            @foreach($links as [$href, $label])
                @php
                    $path = ltrim(parse_url($href, PHP_URL_PATH) ?: $href, '/');
                    $active = $label === 'Basics'
                        ? request()->is('english-basics', 'english-basics/*')
                        : ($label === 'Speak'
                            ? request()->is('speaking', 'speaking/*')
                            : (request()->is($path) || ($href === '/' && request()->is('/'))));
                    if ($label === 'Home' && request()->is('english-basics*')) {
                        $active = false;
                    }
                @endphp
                <a href="{{ $href }}" class="{{ $pill }} px-3.5 py-1.5 text-sm font-semibold {{ $active ? 'bg-brand-soft text-brand-deep' : 'text-ink/70 hover:bg-fog hover:text-ink' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex shrink-0 items-center gap-2">
            <div class="theme-switch flex items-center rounded-full p-0.5" role="group" aria-label="Website theme">
                @foreach(Theme::options() as $key => $meta)
                    <a href="{{ request()->fullUrlWithQuery(['theme' => $key]) }}"
                       class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide sm:px-3 {{ $theme === $key ? 'bg-brand text-white shadow-sm' : 'text-muted hover:text-ink' }}">
                        {{ $meta['label'] }}
                    </a>
                @endforeach
            </div>
            @auth
                <span class="hidden max-w-[9rem] truncate text-sm font-bold text-ink sm:inline">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 {{ $pill }} border border-[rgba(49,36,24,0.1)] bg-paper px-3 py-2 text-sm font-bold text-ink hover:bg-mist sm:px-4">
                        <x-nvs.icon name="LogOut" class="h-4 w-4" />
                        Log out
                    </button>
                </form>
            @else
                <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('nvs-auth-required', { detail: { tab: 'login' } }))"
                        class="inline-flex {{ $pill }} border border-[rgba(49,36,24,0.12)] bg-paper px-3 py-2 text-sm font-bold text-ink hover:bg-mist sm:px-4">
                    Log in
                </button>
                <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('nvs-auth-required', { detail: { tab: 'register' } }))"
                        class="hidden {{ $pill }} bg-brand px-3 py-2 text-sm font-bold text-white shadow-lg shadow-brand/20 sm:inline-flex sm:px-4">
                    Register
                </button>
            @endauth
        </div>
    </div>
</header>
