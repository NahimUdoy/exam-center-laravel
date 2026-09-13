@props(['certificate', 'exam'])
@php
    $gold = '#d4af37';
    $goldDeep = '#9a7b2f';
    $goldLight = '#f0e0a8';
    $ivory = '#fffcf5';
    $deep = '#0a0e17';
    $uid = 'c'.preg_replace('/[^a-z0-9]/i', '', $certificate->slug);
    $accent = $exam->accent ?? '#2563eb';
    $recipient = $certificate->recipient();
    $level = $certificate->level();
    $hours = $certificate->hoursCompleted();
    $modules = $certificate->modules();
    $credId = $certificate->credentialId();
@endphp
<div class="cert-sheet relative mx-auto w-full max-w-5xl overflow-hidden rounded-[2px] shadow-[0_40px_100px_rgba(0,0,0,0.45)] print:shadow-none">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $goldLight }}, {{ $goldDeep }} 20%, {{ $gold }} 40%, {{ $goldLight }} 55%, {{ $goldDeep }} 75%, {{ $gold }} 100%)"></div>
    <div class="absolute inset-[3px]" style="background: {{ $deep }}"></div>
    <div class="absolute inset-[6px]" style="background: linear-gradient(45deg, {{ $goldDeep }}, {{ $goldLight }}, {{ $gold }}, {{ $goldDeep }})"></div>
    <div class="absolute inset-[9px]" style="background: {{ $deep }}"></div>
    <div class="absolute inset-[12px]" style="background: linear-gradient(160deg, {{ $goldLight }} 0%, {{ $gold }} 35%, {{ $goldDeep }} 70%, {{ $goldLight }} 100%)"></div>

    <div class="relative m-[15px] overflow-hidden" style="background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(212,175,55,0.12), transparent 55%), radial-gradient(ellipse 70% 50% at 50% 100%, rgba(10,14,23,0.06), transparent 50%), linear-gradient(165deg, {{ $ivory }} 0%, #f7f1e4 42%, #f0e8d6 100%)">
        <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.07]" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <pattern id="{{ $uid }}-guilloche" width="40" height="40" patternUnits="userSpaceOnUse">
                    <circle cx="20" cy="20" r="18" fill="none" stroke="{{ $goldDeep }}" stroke-width="0.4" />
                    <circle cx="20" cy="20" r="12" fill="none" stroke="{{ $goldDeep }}" stroke-width="0.35" />
                    <circle cx="20" cy="20" r="6" fill="none" stroke="{{ $goldDeep }}" stroke-width="0.3" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#{{ $uid }}-guilloche)" />
        </svg>

        <div class="pointer-events-none absolute bottom-8 left-3 top-8 hidden w-7 sm:block" style="background: linear-gradient(180deg, transparent, {{ $gold }}33, transparent); border-left: 1px solid {{ $gold }}55; border-right: 1px solid {{ $gold }}55">
            <p class="font-cert-display absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 -rotate-90 whitespace-nowrap text-[8px] font-semibold uppercase tracking-[0.45em] text-[#9a7b2f]">Est. MMXXVI · NextVerseSkill</p>
        </div>
        <div class="pointer-events-none absolute bottom-8 right-3 top-8 hidden w-7 sm:block" style="background: linear-gradient(180deg, transparent, {{ $gold }}33, transparent); border-left: 1px solid {{ $gold }}55; border-right: 1px solid {{ $gold }}55">
            <p class="font-cert-display absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rotate-90 whitespace-nowrap text-[8px] font-semibold uppercase tracking-[0.45em] text-[#9a7b2f]">Verified Credential</p>
        </div>

        <div class="pointer-events-none absolute left-1/2 top-[42%] -translate-x-1/2 -translate-y-1/2 select-none font-cert-display text-[9rem] font-bold leading-none text-[#0a0e17] opacity-[0.03] sm:text-[13rem]">NV</div>

        <div class="relative mx-3 my-3 border border-[#d4af37]/30 sm:mx-5 sm:my-5">
            <div class="absolute inset-[3px] border border-[#d4af37]/15"></div>

            @foreach([['left-1 top-1 sm:left-2 sm:top-2', ''], ['right-1 top-1 rotate-90 sm:right-2 sm:top-2', ''], ['bottom-1 left-1 -rotate-90 sm:bottom-2 sm:left-2', ''], ['bottom-1 right-1 rotate-180 sm:bottom-2 sm:right-2', '']] as $i => $corner)
                <svg viewBox="0 0 120 120" class="absolute h-[4.5rem] w-[4.5rem] text-[#d4af37] sm:h-24 sm:w-24 {{ $corner[0] }}" fill="none" aria-hidden="true">
                    <path d="M10 110V42C10 22 22 10 42 10h68" stroke="url(#{{ $uid }}-g{{ $i }})" stroke-width="2" />
                    <path d="M22 110V48C22 30 30 22 48 22h62" stroke="{{ $gold }}" stroke-width="1" opacity="0.7" />
                    <path d="M34 110V54C34 38 38 34 54 34h56" stroke="{{ $goldLight }}" stroke-width="0.75" opacity="0.55" />
                    <circle cx="22" cy="22" r="3.5" fill="{{ $gold }}" />
                    <circle cx="22" cy="22" r="7" stroke="{{ $goldLight }}" stroke-width="0.8" fill="none" />
                    <path d="M36 22c8-10 18-10 26 0M22 36c-10 8-10 18 0 26" stroke="{{ $gold }}" stroke-width="0.9" opacity="0.85" />
                    <path d="M48 22h20M22 48v20" stroke="{{ $goldDeep }}" stroke-width="1.2" />
                    <path d="M70 18c6 0 10 4 10 10M18 70c0 6 4 10 10 10" stroke="{{ $goldLight }}" stroke-width="0.8" />
                    <defs>
                        <linearGradient id="{{ $uid }}-g{{ $i }}" x1="10" y1="10" x2="110" y2="110">
                            <stop stop-color="{{ $goldLight }}" />
                            <stop offset="0.5" stop-color="{{ $gold }}" />
                            <stop offset="1" stop-color="{{ $goldDeep }}" />
                        </linearGradient>
                    </defs>
                </svg>
            @endforeach

            <div class="relative px-4 py-9 text-center sm:px-14 sm:py-12">
                <div class="mb-4 flex items-center justify-center gap-2">
                    <span class="inline-block h-1.5 w-1.5 rotate-45 bg-[#d4af37]"></span>
                    <p class="font-cert-display text-[10px] font-semibold uppercase tracking-[0.42em] text-[#8a6d2f] sm:text-[11px]">NextVerseSkill Academy</p>
                    <span class="inline-block h-1.5 w-1.5 rotate-45 bg-[#d4af37]"></span>
                </div>

                <p class="font-cert-display text-[10px] uppercase tracking-[0.55em] text-[#b8943c] sm:text-xs">Certificate of Excellence</p>

                <h1 class="font-cert-display mt-3 bg-gradient-to-b from-[#0a0e17] via-[#1a2236] to-[#0a0e17] bg-clip-text text-3xl font-semibold leading-[1.15] text-transparent sm:text-5xl">{{ $certificate->title }}</h1>

                <div class="mx-auto mt-4 flex max-w-md items-center justify-center gap-3 py-1">
                    <span class="h-px flex-1 bg-gradient-to-r from-transparent to-[#d4af37]"></span>
                    <svg viewBox="0 0 64 16" class="h-4 w-16" aria-hidden="true">
                        <path d="M2 8h18M44 8h18M32 2l4 6-4 6-4-6 4-6z" fill="none" stroke="{{ $gold }}" stroke-width="1.2" />
                        <circle cx="32" cy="8" r="2.2" fill="{{ $gold }}" />
                    </svg>
                    <span class="h-px flex-1 bg-gradient-to-l from-transparent to-[#d4af37]"></span>
                </div>

                <p class="font-cert-body mt-4 text-lg italic text-[#6b5f4a] sm:text-xl">This distinguished honor is conferred upon</p>

                <p class="font-cert-body mt-2 text-4xl font-semibold tracking-wide sm:text-5xl md:text-6xl" style="background: linear-gradient(180deg, {{ $accent }}, {{ $goldDeep }}); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">{{ $recipient }}</p>

                <div class="mx-auto mt-2 h-[3px] w-48 rounded-full bg-gradient-to-r from-transparent via-[#d4af37] to-transparent sm:w-64"></div>
                <div class="mx-auto mt-1 h-px w-28 bg-gradient-to-r from-transparent via-[#f0e0a8] to-transparent"></div>

                <p class="font-cert-body mx-auto mt-5 max-w-2xl text-base leading-relaxed text-[#3d362c] sm:text-[1.15rem]">
                    for exemplary commitment and mastery across the <span class="font-semibold text-[#0a0e17]">{{ $exam->name }}</span> learning pathway. {{ $certificate->subtitle }}
                </p>

                <div class="mx-auto mt-8 grid max-w-3xl grid-cols-3 gap-2 sm:gap-5">
                    @foreach([[$certificate->score_label ?: 'Score', $certificate->score_value ?: '—'], ['Honors Class', $level], ['Guided Hours', (string) $hours]] as $stat)
                        <div class="relative overflow-hidden px-2 py-4" style="background: linear-gradient(180deg, rgba(212,175,55,0.08), rgba(212,175,55,0.02)); border-top: 1px solid {{ $gold }}66; border-bottom: 1px solid {{ $gold }}66">
                            <p class="font-cert-display text-[8px] font-semibold uppercase tracking-[0.22em] text-[#9a7b2f] sm:text-[10px]">{{ $stat[0] }}</p>
                            <p class="font-cert-display mt-1.5 text-xl font-semibold text-[#0a0e17] sm:text-3xl">{{ $stat[1] }}</p>
                        </div>
                    @endforeach
                </div>

                @if(count($modules))
                    <div class="mx-auto mt-6 flex max-w-2xl flex-wrap justify-center gap-2">
                        @foreach($modules as $module)
                            <span class="font-cert-body border px-3 py-1 text-[11px] tracking-wide text-[#5c5346]" style="border-color: {{ $gold }}66; background: rgba(255,252,245,0.7)">{{ $module }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mx-auto mt-10 grid max-w-3xl items-end gap-6 sm:grid-cols-[1fr_auto_1fr] sm:gap-4">
                    <div class="order-2 text-center sm:order-1 sm:text-left">
                        <p class="font-cert-body mb-1 text-2xl italic text-[#0a0e17]/85 sm:text-3xl">Aria Vale</p>
                        <div class="mx-auto h-px w-40 bg-gradient-to-r from-[#d4af37] to-transparent sm:mx-0"></div>
                        <p class="font-cert-display mt-2 text-[9px] font-semibold uppercase tracking-[0.22em] text-[#9a7b2f] sm:text-[10px]">Chancellor · Academy</p>
                    </div>

                    <div class="order-1 sm:order-2">
                        <div class="relative mx-auto h-36 w-36 sm:h-40 sm:w-40">
                            <div class="absolute inset-0 rounded-full opacity-40 blur-md" style="background: {{ $gold }}"></div>
                            <div class="absolute inset-0 rounded-full shadow-[0_12px_30px_rgba(10,14,23,0.45)]" style="background: conic-gradient(from 20deg, {{ $goldLight }}, {{ $goldDeep }}, {{ $gold }}, {{ $goldLight }}, {{ $goldDeep }}, {{ $gold }})"></div>
                            <div class="absolute inset-[5px] rounded-full" style="background: {{ $deep }}"></div>
                            <div class="absolute inset-[9px] rounded-full border" style="border-color: {{ $gold }}99; background: radial-gradient(circle at 35% 30%, #1a2236, {{ $deep }})"></div>
                            <div class="absolute inset-[14px] rounded-full border border-dashed border-[#d4af37]/35"></div>
                            <div class="absolute inset-0 grid place-items-center text-center">
                                <div>
                                    <p class="font-cert-display text-2xl font-bold tracking-[0.15em] text-[#f0e0a8]">NV</p>
                                    <p class="mt-0.5 text-[8px] uppercase tracking-[0.28em] text-[#d4af37]">Excellence</p>
                                </div>
                            </div>
                            <div class="absolute -bottom-4 left-1/2 h-12 w-5 -translate-x-[130%] rotate-[-22deg] shadow-md" style="background: linear-gradient(180deg, {{ $accent }}, {{ $deep }}); clip-path: polygon(0 0, 100% 0, 100% 78%, 50% 100%, 0 78%)"></div>
                            <div class="absolute -bottom-4 left-1/2 h-12 w-5 translate-x-[30%] rotate-[22deg] shadow-md" style="background: linear-gradient(180deg, {{ $goldLight }}, {{ $goldDeep }}); clip-path: polygon(0 0, 100% 0, 100% 78%, 50% 100%, 0 78%)"></div>
                        </div>
                    </div>

                    <div class="order-3 text-center sm:text-right">
                        <p class="font-cert-body mb-1 text-2xl italic text-[#0a0e17]/85 sm:text-3xl">{{ $certificate->issued_on ?: 'Pending' }}</p>
                        <div class="mx-auto h-px w-40 bg-gradient-to-l from-[#d4af37] to-transparent sm:ml-auto sm:mr-0"></div>
                        <p class="font-cert-display mt-2 text-[9px] font-semibold uppercase tracking-[0.22em] text-[#9a7b2f] sm:text-[10px]">Date of Conferral</p>
                    </div>
                </div>

                <div class="mt-10 border-t border-[#d4af37]/25 pt-5">
                    <p class="font-cert-display text-[10px] tracking-[0.18em] text-[#8a6d2f] sm:text-[11px]">AUTHENTICITY · {{ $credId }}</p>
                    <p class="font-cert-body mx-auto mt-2 max-w-lg text-[10px] leading-relaxed text-[#8a8070] sm:text-[11px]">
                        Issued under the authority of NextVerseSkill Academy. A practice credential recognizing learning excellence — not an official {{ $exam->short_name }} score report from the test provider.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
