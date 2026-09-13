@php
    use App\Models\Exam;
    use App\Support\Cms\Site;
    use App\Support\ExamContext;
    $exams = Exam::query()->orderBy('id')->get();
    $current = ExamContext::current(request('exam'));
    $marketing = ($variant ?? 'marketing') === 'marketing';
    $site = $site ?? Site::get();
    $brand = $site['brandName'] ?? 'NextVerseSkill';
@endphp
<header class="sticky top-0 z-50 border-b border-[rgba(15,40,80,0.08)] {{ $marketing ? 'bg-white/90' : 'bg-paper/95' }} backdrop-blur-xl">
    @unless($marketing)
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
    @endunless

    <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-4 px-4 sm:h-16 sm:px-6">
        <a href="{{ route('academy') }}" class="font-display inline-flex items-center gap-2.5 font-bold tracking-tight text-ink">
            @if(!empty($site['logoImage']))
                <img src="{{ $site['logoImage'] }}" alt="{{ $brand }}" class="h-9 w-9 rounded-xl object-cover">
            @else
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-deep text-[11px] font-extrabold text-white shadow-lg shadow-brand/25">NV</span>
            @endif
            <span class="leading-tight">
                <span class="block text-[0.95rem] sm:text-[1.15rem]">NextVerse<span class="bg-gradient-to-r from-brand to-indigo-600 bg-clip-text text-transparent">Skill</span></span>
                <span class="block text-[0.65rem] font-extrabold uppercase tracking-[0.14em] text-brand sm:text-[0.72rem]">Academy</span>
            </span>
        </a>

        <nav class="hidden items-center gap-0.5 lg:flex">
            @php
                $basicsUrl = route('english-basics.index', ['exam' => 'english-basic']);
                $links = $marketing
                    ? [['/', 'Home'], ['/academy', 'Academy'], [$basicsUrl, 'Basics'], ['/dashboard', 'Practice'], ['/mock-tests', 'Mock Tests'], ['/certificates', 'Certificates'], ['/#courses', 'All Courses']]
                    : [['/dashboard', 'Home'], ['/academy', 'Academy'], [$basicsUrl, 'Basics'], ['/dashboard', 'Practice'], ['/mock-tests', 'Mock Tests'], ['/certificates', 'Certificates']];
            @endphp
            @foreach($links as [$href, $label])
                @php
                    $path = ltrim(parse_url($href, PHP_URL_PATH) ?: $href, '/');
                    $active = $label === 'Basics'
                        ? request()->is('english-basics', 'english-basics/*')
                        : (request()->is($path) || ($href === '/' && request()->is('/')));
                    if ($label === 'Home' && request()->is('english-basics*')) {
                        $active = false;
                    }
                @endphp
                <a href="{{ $href }}" class="rounded-full px-3.5 py-1.5 text-sm font-semibold {{ $active ? 'bg-brand-soft text-brand-deep' : 'text-ink/70 hover:bg-fog hover:text-ink' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex shrink-0 items-center gap-2">
            @auth
                <span class="hidden max-w-[9rem] truncate text-sm font-bold text-ink sm:inline">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-[rgba(15,40,80,0.08)] bg-white px-3 py-2 text-sm font-bold text-ink hover:bg-mist sm:px-4">
                        <x-nvs.icon name="LogOut" class="h-4 w-4" />
                        Log out
                    </button>
                </form>
            @else
                <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('nvs-auth-required', { detail: { tab: 'login' } }))"
                        class="inline-flex rounded-full border border-[rgba(15,40,80,0.12)] bg-white px-3 py-2 text-sm font-bold text-ink hover:bg-mist sm:px-4">
                    Log in
                </button>
                <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('nvs-auth-required', { detail: { tab: 'register' } }))"
                        class="inline-flex rounded-full bg-gradient-to-r from-brand to-brand-deep px-3 py-2 text-sm font-bold text-white shadow-lg shadow-brand/25 sm:px-4">
                    Register
                </button>
            @endauth
        </div>
    </div>
</header>
