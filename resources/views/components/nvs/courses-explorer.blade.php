@props(['courses', 'site'])
<section id="courses" class="courses-stage relative overflow-hidden px-4 py-20 sm:px-6 lg:py-28">
    <div class="pointer-events-none absolute inset-0" aria-hidden>
        <div class="courses-grid-pan absolute inset-0 opacity-40"></div>
        <div class="course-orb absolute -left-24 top-10 h-72 w-72 rounded-full bg-rose-500/30 blur-3xl"></div>
        <div class="course-orb absolute right-[-4rem] top-24 h-80 w-80 rounded-full bg-sky-400/25 blur-3xl" style="animation-delay: 2.4s"></div>
        <div class="course-orb absolute bottom-10 left-1/3 h-64 w-64 rounded-full bg-emerald-400/20 blur-3xl" style="animation-delay: 4s"></div>
    </div>
    <div class="relative mx-auto max-w-7xl">
        <div class="mx-auto max-w-3xl animate-rise text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-sky-200 backdrop-blur">
                {{ $site['coursesEyebrow'] ?? '4 tracks · 660+ free sets' }}
            </span>
            <h2 class="font-display mt-5 text-3xl font-extrabold text-white sm:text-5xl">
                {{ $site['coursesTitle'] ?? 'Explore Our Courses' }}
                <span class="mt-1 block bg-gradient-to-r from-sky-300 via-white to-violet-300 bg-clip-text text-transparent">Practice for Your Dream</span>
            </h2>
            <p class="mt-4 text-base text-white/65 sm:text-lg">{{ $site['coursesSubtitle'] ?? '' }}</p>
            <p class="hand-note mt-4 text-2xl text-sky-300">{{ $site['heroNote'] ?? '' }}</p>
        </div>

        @php $ticker = $site['ticker'] ?? []; $loopTicker = array_merge($ticker, $ticker); @endphp
        <div class="courses-marquee mt-10 overflow-hidden rounded-full border border-white/10 bg-white/5 py-2.5">
            <div class="courses-marquee-track flex w-max gap-3 pr-3">
                @foreach($loopTicker as $item)
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-bold text-white/80">
                        <x-nvs.icon name="Sparkles" class="h-3 w-3 text-sky-300" />
                        {{ $item }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($courses as $index => $course)
                @php $max = max(1, ...$course->modules->pluck('value')->all() ?: [1]); @endphp
                <a href="{{ route('dashboard', ['exam' => $course->slug]) }}"
                   class="course-card course-card-in group relative flex flex-col overflow-hidden rounded-[1.6rem] border border-white/20 bg-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.22)] backdrop-blur-xl transition duration-500 hover:-translate-y-2"
                   style="--course: {{ $course->color }}; --course-soft: {{ $course->soft }}; --course-glow: {{ $course->glow }}; --i: {{ $index }}"
                   x-data
                   @mousemove="const b = $el.getBoundingClientRect(); $el.style.setProperty('--mx', ($event.clientX - b.left) + 'px'); $el.style.setProperty('--my', ($event.clientY - b.top) + 'px')">
                    <span class="course-halo" aria-hidden></span>
                    <span class="course-spotlight" aria-hidden></span>
                    <div class="relative h-36 overflow-hidden" style="background: {{ $course->cover_image ? 'url('.$course->cover_image.') center/cover' : 'linear-gradient(145deg, '.$course->color.', #0b1f44 78%)' }}">
                        <div class="course-mesh absolute inset-0"></div>
                        <div class="relative z-10 flex h-full flex-col justify-between p-5">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full border border-white/20 bg-white/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white/90">{{ $course->tag }}</span>
                                <span class="rounded-full bg-emerald-400/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-200">Free</span>
                            </div>
                            <p class="font-display text-3xl font-extrabold text-white drop-shadow-sm">{{ $course->name }}</p>
                        </div>
                    </div>
                    <div class="relative z-10 flex flex-1 flex-col p-5">
                        <ul class="space-y-2">
                            @foreach($course->modules as $i => $module)
                                <li class="course-module rounded-xl px-2 py-1.5" style="--mi: {{ $i }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="flex min-w-0 items-center gap-2.5">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-white" style="background: {{ $course->color }}33; color: {{ $course->color }}">
                                                <x-nvs.icon :name="$module->icon ?? 'BookOpen'" class="h-3.5 w-3.5" />
                                            </span>
                                            <span class="text-[13px] font-semibold leading-tight text-white">{{ $module->name }}</span>
                                        </span>
                                        <span class="shrink-0 text-xs font-bold text-white/55">{{ $module->count_label }}</span>
                                    </div>
                                    <span class="mt-1.5 block h-1 overflow-hidden rounded-full bg-white/10">
                                        <span class="course-bar block h-full rounded-full" style="width: {{ ($module->value / $max) * 100 }}%; background: {{ $course->color }}"></span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <span class="course-cta mt-5 inline-flex items-center justify-center gap-2 rounded-xl py-3 text-center text-sm font-bold text-white">
                            Total {{ $course->total }}
                            <x-nvs.icon name="ArrowRight" class="h-4 w-4" />
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
