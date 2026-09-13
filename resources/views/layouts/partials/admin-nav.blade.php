@php
    $nav = [
        ['href' => route('admin.overview'), 'match' => 'admin.overview', 'label' => 'Overview', 'icon' => 'LayoutDashboard'],
        ['href' => route('admin.collection', 'exams'), 'match' => 'exams', 'label' => 'Exams', 'icon' => 'GraduationCap'],
        ['href' => route('admin.collection', 'courses'), 'match' => 'courses', 'label' => 'Homepage courses', 'icon' => 'Layers'],
        ['href' => route('admin.collection', 'skills'), 'match' => 'skills', 'label' => 'Skills & colors', 'icon' => 'Sparkles'],
        ['href' => route('admin.collection', 'tasks'), 'match' => 'tasks', 'label' => 'Practice tasks', 'icon' => 'Target'],
        ['href' => route('admin.collection', 'questions'), 'match' => 'questions', 'label' => 'Practice questions', 'icon' => 'BookOpen'],
        ['href' => route('admin.collection', 'mocks'), 'match' => 'mocks', 'label' => 'Mock tests', 'icon' => 'Trophy'],
        ['href' => route('admin.collection', 'mock-sections'), 'match' => 'mock-sections', 'label' => 'Mock section colors', 'icon' => 'Sparkles'],
        ['href' => route('admin.collection', 'certificates'), 'match' => 'certificates', 'label' => 'Certificates', 'icon' => 'Award'],
        ['href' => route('admin.collection', 'cert-categories'), 'match' => 'cert-categories', 'label' => 'Certificate types', 'icon' => 'Layers'],
        ['href' => route('admin.collection', 'pte'), 'match' => 'pte', 'label' => 'PTE question bank', 'icon' => 'Languages'],
        ['href' => route('admin.collection', 'pte-types'), 'match' => 'pte-types', 'label' => 'PTE task types', 'icon' => 'Sparkles'],
        ['href' => route('admin.collection', 'diagnostics'), 'match' => 'diagnostics', 'label' => 'Level-check questions', 'icon' => 'Compass'],
        ['href' => route('admin.collection', 'reading-passages'), 'match' => 'reading-passages', 'label' => 'Reading passages', 'icon' => 'BookOpen'],
        ['href' => route('admin.collection', 'reading-questions'), 'match' => 'reading-questions', 'label' => 'Reading questions', 'icon' => 'BookOpen'],
        ['href' => route('admin.site'), 'match' => 'admin.site', 'label' => 'Site & Academy', 'icon' => 'Settings'],
        ['href' => route('admin.media'), 'match' => 'admin.media', 'label' => 'Media library', 'icon' => 'Image'],
    ];
@endphp
<div class="flex h-full flex-col">
    <div class="flex items-center justify-between px-5 py-5">
        <a href="{{ route('home') }}" class="font-display text-lg font-extrabold text-white">NextVerse<span class="text-sky-300">Skill</span></a>
        <span class="rounded-full bg-white/10 px-2 py-1 text-[10px] font-extrabold uppercase tracking-wider">Admin</span>
    </div>
    <nav class="space-y-0.5 px-3 pb-6">
        @foreach($nav as $item)
            @php
                $active = $item['match'] === 'admin.overview'
                    ? request()->routeIs('admin.overview')
                    : ($item['match'] === 'admin.site'
                        ? request()->routeIs('admin.site')
                        : ($item['match'] === 'admin.media'
                            ? request()->routeIs('admin.media')
                            : request()->routeIs('admin.collection*') && request()->route('collection') === $item['match']));
            @endphp
            <a href="{{ $item['href'] }}"
               class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-semibold {{ $active ? 'bg-white text-ink' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                <x-nvs.icon :name="$item['icon']" class="h-4 w-4" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
    <div class="mt-auto border-t border-white/10 px-3 py-4">
        <a href="{{ route('home') }}" target="_blank" class="mb-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-sky-200">View website</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-white/60">Sign out</button>
        </form>
    </div>
</div>
