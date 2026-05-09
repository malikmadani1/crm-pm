@php
    $isRtl = app()->isLocale('ar');
    $languageSwitchLocale = $isRtl ? 'en' : 'ar';
    $languageSwitchFlagClass = $languageSwitchLocale === 'en' ? 'fi-gb' : 'fi-sy';
    $languageSwitchFlag = $languageSwitchLocale === 'en' ? '🇬🇧' : '🇸🇾';
    $languageSwitchCode = strtoupper($languageSwitchLocale);
    $languageSwitchTitle = $isRtl ? __('English') : 'العربية';
    $languageSwitchLabel = $isRtl ? __('English') : 'العربية';
    $navSections = [
        __('Overview') => [
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Notifications', 'route' => 'notifications.index'],
            ['label' => 'Audit Log', 'route' => 'audit-logs.index'],
        ],
        __('CRM') => [
            ['label' => 'Customers', 'route' => 'customers.index'],
            ['label' => 'Leads', 'route' => 'leads.index'],
            ['label' => 'Deals', 'route' => 'deals.index'],
            ['label' => 'Pipeline', 'route' => 'deals.pipeline'],
        ],
        __('Projects') => [
            ['label' => 'Projects', 'route' => 'projects.index'],
            ['label' => 'Tasks', 'route' => 'tasks.index'],
            ['label' => 'Kanban Board', 'route' => 'kanban.index'],
        ],
        __('Reports') => [
            ['label' => 'Sales', 'route' => 'reports.sales'],
            ['label' => 'CRM Reports', 'route' => 'reports.crm'],
            ['label' => 'Project Reports', 'route' => 'reports.projects'],
            ['label' => 'Task Reports', 'route' => 'reports.tasks'],
            ['label' => 'Team Reports', 'route' => 'reports.team'],
            ['label' => 'Attendance', 'route' => 'attendance.index'],
        ],
        __('Administration') => [
            ['label' => 'Users', 'route' => 'users.index'],
            ['label' => 'Roles', 'route' => 'roles.index'],
            ['label' => 'Settings', 'route' => 'settings.index'],
            ['label' => 'Profile', 'route' => 'profile.edit'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'CRM & PM System') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
        <script>
            (() => {
                const storedTheme = localStorage.getItem('theme');
                const theme = storedTheme === 'light' || storedTheme === 'dark'
                    ? storedTheme
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

                document.documentElement.classList.remove('light', 'dark');
                document.documentElement.classList.add(theme);
                document.documentElement.style.colorScheme = theme;
            })();
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,800|instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="app-body h-full bg-slate-950 text-slate-100"
        x-data="{
            sidebarOpen: false,
            notificationsOpen: false,
            userOpen: false,
            theme: document.documentElement.classList.contains('light') ? 'light' : 'dark',
            applyTheme(value) {
                document.documentElement.classList.remove('light', 'dark');
                document.documentElement.classList.add(value);
                document.documentElement.style.colorScheme = value;
                localStorage.setItem('theme', value);
            },
        }"
        x-init="applyTheme(theme); $watch('theme', value => applyTheme(value));"
    >
        <x-toast-stack />

        <div class="app-shell min-h-screen">
            <div class="mx-auto flex min-h-screen max-w-[1800px]">
                <aside
                    class="app-sidebar fixed inset-y-0 z-40 flex h-screen w-72 flex-col bg-slate-950/80 px-5 py-6 backdrop-blur-xl transition-transform duration-300 lg:sticky lg:top-0 lg:translate-x-0 {{ $isRtl ? 'right-0 border-l border-white/10' : 'left-0 border-r border-white/10' }}"
                    :class="sidebarOpen ? 'translate-x-0' : '{{ $isRtl ? 'translate-x-full lg:translate-x-0' : '-translate-x-full lg:translate-x-0' }}'"
                >
                    <div class="flex items-center justify-between">
                        <a href="{{ route('dashboard') }}" class="block">
                            <div class="text-sm uppercase tracking-[0.35em] text-cyan-300">{{ __('SaaS Suite') }}</div>
                            <div class="mt-1 text-xl font-semibold text-white">CRM &amp; PM System</div>
                        </a>
                        <button class="rounded-xl border border-white/10 p-2 text-slate-300 lg:hidden" @click="sidebarOpen = false" aria-label="{{ __('Close menu') }}">&times;</button>
                    </div>

                    <nav class="app-scrollbar mt-10 flex-1 space-y-8 overflow-y-auto pe-1">
                        @foreach ($navSections as $section => $items)
                            <div>
                                <div class="mb-3 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">{{ $section }}</div>
                                <div class="space-y-1.5">
                                    @foreach ($items as $item)
                                        <a
                                            href="{{ route($item['route']) }}"
                                            class="app-nav-link flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'bg-cyan-500/15 text-white shadow-lg shadow-cyan-500/10' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                                        >
                                            <span>{{ __($item['label']) }}</span>
                                            @if (request()->routeIs($item['route']))
                                                <span class="h-2.5 w-2.5 rounded-full bg-cyan-400"></span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </aside>

                <div class="flex min-h-screen flex-1 flex-col lg:pl-0">
                    <header class="app-header sticky top-0 z-30 border-b border-white/10 bg-slate-950/60 backdrop-blur-xl">
                        <div class="flex items-center gap-4 px-4 py-4 sm:px-6 lg:px-10">
                            <button class="rounded-2xl border border-white/10 p-3 text-slate-200 lg:hidden" @click="sidebarOpen = true" aria-label="{{ __('Open menu') }}">&#9776;</button>

                            <div class="relative hidden flex-1 md:block">
                                <input
                                    type="text"
                                    placeholder="{{ __('Search customers, leads, projects, tasks...') }}"
                                    class="app-search-input w-full rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30"
                                >
                            </div>

                            <div class="{{ $isRtl ? 'mr-auto' : 'ml-auto' }} flex items-center gap-3">
                                @auth
                                    <div class="hidden xl:flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-3 py-2">
                                        <div class="text-xs {{ $isRtl ? 'text-right' : 'text-left' }}">
                                            <div class="font-semibold text-white">
                                                @if($appTodayAttendance?->checked_in_at && ! $appTodayAttendance?->checked_out_at)
                                                    {{ __('Checked in') }}
                                                @elseif($appTodayAttendance?->checked_out_at)
                                                    {{ __('Checked out') }}
                                                @else
                                                    {{ __('Not started') }}
                                                @endif
                                            </div>
                                            <div class="mt-1 text-slate-400">
                                                @if($appTodayAttendance?->checked_in_at && ! $appTodayAttendance?->checked_out_at)
                                                    {{ $appTodayAttendance->checked_in_at?->format('H:i') }}
                                                @elseif($appTodayAttendance?->checked_out_at)
                                                    {{ $appTodayAttendance->checked_in_at?->format('H:i') }} - {{ $appTodayAttendance->checked_out_at?->format('H:i') }}
                                                @else
                                                    {{ __('Start your work day') }}
                                                @endif
                                            </div>
                                        </div>

                                        @if($appTodayAttendance?->checked_in_at && ! $appTodayAttendance?->checked_out_at)
                                            <form method="POST" action="{{ route('attendance.check-out') }}">
                                                @csrf
                                                <button class="rounded-2xl bg-rose-500/15 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/25">إنهاء الدوام</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('attendance.check-in') }}">
                                                @csrf
                                                <button class="rounded-2xl bg-emerald-500/15 px-3 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/25">بدء الدوام</button>
                                            </form>
                                        @endif
                                    </div>
                                @endauth

                                <a
                                    href="{{ route('locale.update', $languageSwitchLocale) }}"
                                    class="header-control group"
                                    title="{{ $languageSwitchTitle }}"
                                    aria-label="{{ $languageSwitchTitle }}"
                                >
                                    <span class="header-control-flag" aria-hidden="true">
                                        <span class="fi {{ $languageSwitchFlagClass }}"></span>
                                    </span>
                                    <span class="header-control-code">{{ $languageSwitchCode }}</span>
                                </a>

                                <button
                                    class="header-control"
                                    @click="theme = theme === 'dark' ? 'light' : 'dark'"
                                    :title="theme === 'dark' ? '{{ __('Light mode') }}' : '{{ __('Dark mode') }}'"
                                    :aria-label="theme === 'dark' ? '{{ __('Light mode') }}' : '{{ __('Dark mode') }}'"
                                >
                                    <span class="header-control-icon" x-show="theme === 'dark'" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.2M12 18.8V21M5.64 5.64l1.56 1.56M16.8 16.8l1.56 1.56M3 12h2.2M18.8 12H21M5.64 18.36 7.2 16.8M16.8 7.2l1.56-1.56" />
                                            <circle cx="12" cy="12" r="4.2" />
                                        </svg>
                                    </span>
                                    <span class="header-control-icon" x-show="theme !== 'dark'" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.2 15.1A8.7 8.7 0 0 1 8.9 3.8a9 9 0 1 0 11.3 11.3Z" />
                                        </svg>
                                    </span>
                                </button>

                                <div class="relative" @click.outside="notificationsOpen = false">
                                    <button class="header-control relative" @click="notificationsOpen = !notificationsOpen" title="{{ __('Notifications') }}" aria-label="{{ __('Notifications') }}">
                                        <span class="header-control-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17H9m9-1V11a6 6 0 1 0-12 0v5l-1.5 2.2A1 1 0 0 0 5.32 20h13.36a1 1 0 0 0 .82-1.8L18 16Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20a2 2 0 0 0 4 0" />
                                            </svg>
                                        </span>
                                        @if($appUnreadNotificationsCount)
                                            <span class="header-control-badge">{{ $appUnreadNotificationsCount }}</span>
                                        @endif
                                    </button>
                                    <div x-show="notificationsOpen" x-transition class="app-dropdown absolute mt-3 w-96 rounded-3xl border border-white/10 bg-slate-900/95 p-4 shadow-2xl {{ $isRtl ? 'left-0' : 'right-0' }}">
                                        <div class="mb-4 flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-white">{{ __('Recent notifications') }}</h3>
                                            <form action="{{ route('notifications.read-all') }}" method="POST">
                                                @csrf
                                                <button class="text-xs text-cyan-300">{{ __('Mark all read') }}</button>
                                            </form>
                                        </div>
                                        <div class="space-y-3">
                                            @forelse(auth()->user()->notifications()->latest()->limit(5)->get() as $notification)
                                                @php($hasUrl = ! empty($notification->data['url']))
                                                @if($hasUrl)
                                                    <a href="{{ route('notifications.open', $notification) }}" class="notification-card-link block rounded-2xl border border-white/10 bg-white/5 p-3">
                                                        <div class="text-sm font-semibold text-white">{{ __($notification->data['title'] ?? 'Notification') }}</div>
                                                        <div class="mt-1 text-xs text-slate-400">{{ __($notification->data['message'] ?? '') }}</div>
                                                    </a>
                                                @else
                                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                                        <div class="text-sm font-semibold text-white">{{ __($notification->data['title'] ?? 'Notification') }}</div>
                                                        <div class="mt-1 text-xs text-slate-400">{{ __($notification->data['message'] ?? '') }}</div>
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="rounded-2xl border border-dashed border-white/10 p-4 text-center text-sm text-slate-400">{{ __('No notifications yet.') }}</div>
                                            @endforelse
                                        </div>
                                        <a href="{{ route('notifications.index') }}" class="mt-4 block text-center text-sm font-medium text-cyan-300">{{ __('Open all notifications') }}</a>
                                    </div>
                                </div>

                                <div class="relative" @click.outside="userOpen = false">
                                    <button class="app-topbar-button flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-2 hover:bg-white/10" @click="userOpen = !userOpen">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400/20 via-cyan-300/10 to-sky-500/20 text-cyan-300 ring-1 ring-cyan-400/15">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20a7.5 7.5 0 0 1 15 0" />
                                            </svg>
                                        </div>
                                        <div class="hidden sm:block {{ $isRtl ? 'text-right' : 'text-left' }}">
                                            <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                                            <div class="text-xs text-slate-400">{{ collect(auth()->user()->role_names)->map(fn ($roleName) => __($roleName))->join(', ') ?: (auth()->user()->job_title ? __(auth()->user()->job_title) : '') }}</div>
                                        </div>
                                    </button>
                                    <div x-show="userOpen" x-transition class="app-dropdown absolute mt-3 w-64 rounded-3xl border border-white/10 bg-slate-900/95 p-3 shadow-2xl {{ $isRtl ? 'left-0' : 'right-0' }}">
                                        <a href="{{ route('profile.edit') }}" class="block rounded-2xl px-4 py-3 text-sm text-slate-200 hover:bg-white/5">{{ __('Profile settings') }}</a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button class="mt-2 block w-full rounded-2xl bg-rose-500/10 px-4 py-3 text-sm text-rose-300 hover:bg-rose-500/20 {{ $isRtl ? 'text-right' : 'text-left' }}">{{ __('Sign out') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10">
                        @isset($header)
                            <div class="mb-6">
                                {{ $header }}
                            </div>
                        @endisset

                        {{ $slot }}
                    </main>

                    <footer class="app-footer border-t border-white/10 px-4 py-5 text-center text-xs text-slate-500 sm:px-6 lg:px-10">
                        {{ __('CRM & PM System') }} | {{ __('Laravel Monolith') }} | {{ __('Built for production workflows') }}
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>

