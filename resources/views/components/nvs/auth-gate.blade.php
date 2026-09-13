@guest
@php
    $authOpen = session('auth_required') || ($errors->any() && old('auth_tab'));
    $authTab = old('auth_tab', session('auth_tab', 'login'));
@endphp
<div x-data="{
        open: {{ $authOpen ? 'true' : 'false' }},
        tab: {{ Js::from($authTab) }},
        intended: {{ Js::from(url()->full()) }}
     }"
     x-on:nvs-auth-required.window="open = true; tab = ($event.detail && $event.detail.tab) ? $event.detail.tab : 'login'">
    <div x-show="open" x-cloak class="nvs-modal-layer fixed inset-0 z-[200] flex items-end justify-center p-0 sm:items-center sm:p-6" role="dialog" aria-modal="true">
        <button type="button" class="absolute inset-0 bg-[#0b1f44]/65 backdrop-blur-md" @click="open = false" aria-label="Close"></button>
        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-t-[1.75rem] bg-white shadow-premium-lg sm:rounded-[1.75rem]" @click.stop>
            <div class="bg-gradient-to-br from-[#0b1f44] via-[#123066] to-brand px-6 pb-6 pt-5 text-white">
                <button type="button" @click="open = false" class="absolute right-4 top-4 grid h-8 w-8 place-items-center rounded-full bg-white/10 text-sm">✕</button>
                <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-sky-200">Account needed</p>
                <h2 class="font-display mt-2 text-2xl font-extrabold">Log in to open questions</h2>
                <p class="mt-1 text-sm text-white/70">Browse every task type for free. Log in to see the questions and submit your answers.</p>
            </div>
            <div class="grid grid-cols-2 gap-1 bg-mist p-1">
                <button type="button" class="rounded-xl py-2 text-sm font-bold" :class="tab === 'login' ? 'bg-white text-ink shadow-sm' : 'text-muted'" @click="tab = 'login'">Log in</button>
                <button type="button" class="rounded-xl py-2 text-sm font-bold" :class="tab === 'register' ? 'bg-white text-ink shadow-sm' : 'text-muted'" @click="tab = 'register'">Create account</button>
            </div>
            @if ($errors->any())
                <p class="mx-5 mt-4 rounded-xl bg-rose/10 px-3 py-2 text-sm font-semibold text-rose">{{ $errors->first() }}</p>
            @endif
            <form x-show="tab === 'login'" method="POST" action="{{ route('login') }}" class="space-y-3 px-5 py-5">
                @csrf
                <input type="hidden" name="intended" :value="intended">
                <input type="hidden" name="auth_tab" value="login">
                <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Email
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm text-ink outline-none ring-brand/30 focus:ring-2">
                </label>
                <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Password
                    <input type="password" name="password" required autocomplete="current-password" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm text-ink outline-none ring-brand/30 focus:ring-2">
                </label>
                <label class="flex items-center gap-2 text-sm text-muted">
                    <input type="checkbox" name="remember" class="rounded border-fog text-brand"> Remember me
                </label>
                <button type="submit" class="w-full rounded-full bg-ink py-3 text-sm font-bold text-white">Log in and continue</button>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="block text-center text-xs font-semibold text-muted">Forgot password?</a>
                @endif
            </form>
            <form x-show="tab === 'register'" x-cloak method="POST" action="{{ route('register') }}" class="space-y-3 px-5 py-5">
                @csrf
                <input type="hidden" name="intended" :value="intended">
                <input type="hidden" name="auth_tab" value="register">
                <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Name
                    <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm text-ink outline-none ring-brand/30 focus:ring-2">
                </label>
                <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Email
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm text-ink outline-none ring-brand/30 focus:ring-2">
                </label>
                <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Password
                    <input type="password" name="password" required autocomplete="new-password" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm text-ink outline-none ring-brand/30 focus:ring-2">
                </label>
                <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Confirm password
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm text-ink outline-none ring-brand/30 focus:ring-2">
                </label>
                <button type="submit" class="w-full rounded-full bg-ink py-3 text-sm font-bold text-white">Create account and continue</button>
            </form>
        </div>
    </div>
</div>
<script>
    window.nvsNeedAuth = function () {
        window.dispatchEvent(new CustomEvent('nvs-auth-required'));
        return true;
    };
</script>
@else
<script>
    window.nvsNeedAuth = function () { return false; };
</script>
@endguest
